set -eu

function valid_response() {
    RESULT_LENGTH="`echo "$1" | jq length`"
    if [ "$?" -eq 0 ] && [ "$RESULT_LENGTH" -gt 0 ]; then
        VALID=0
    else
        VALID=1
    fi
}

function valid_batch_response() {
    INPUT_FILE=$1
    DIRECTORY=$2
    TOTAL_RESULTS=0
    for f in ${DIRECTORY}/*.json; do
        RESULT_LENGTH="`cat $f | jq length`"
        TOTAL_RESULTS=$(($TOTAL_RESULTS + $RESULT_LENGTH))
    done
    if [ "$?" -ne 0 ] || [ "$TOTAL_RESULTS" -eq 0 ] || [ "$TOTAL_RESULTS" -ne "`wc -l $INPUT_FILE | awk '{print $1}'`" ]; then
        VALID=1
    else
        VALID=0
    fi
}

CODE=0
QUERY='1714 14th St NW Washington, DC 20009'
PYTHONS='python2.7 python3'

# ----- setup -----

# python
for dir in 'python' 'batch_python'; do
    for PYTHONX in $PYTHONS; do
        virtualenv -p `which $PYTHONX` $(dirname $0)/../${dir}/.virtualenv_${PYTHONX}
        if [ -e "$(dirname $0)/../${dir}/requirements.txt" ]; then
            $(dirname $0)/../${dir}/.virtualenv_${PYTHONX}/bin/pip install -r $(dirname $0)/../${dir}/requirements.txt
        fi
    done
done

# node
for dir in 'node' 'batch_node'; do
    if [ -e "$(dirname $0)/../${dir}/package.json" ]; then
        cd "$(dirname $0)/../${dir}/" && npm install && cd ..
    fi
done

# ----- basic geocoding -----

# python
for PYTHONX in $PYTHONS; do
    RESPONSE="`$(dirname $0)/../python/.virtualenv_${PYTHONX}/bin/python $(dirname $0)/../python/mapbox_geocode.py "$QUERY"`"
    valid_response "$RESPONSE"
    if [ "$VALID" -ne 0 ]; then
        echo "not ok: python/mapbox_geocode.py using $PYTHONX"
        CODE=1
    else
        echo "ok: python/mapbox_geocode.py using $PYTHONX"
    fi
done

# node
RESPONSE="`node $(dirname $0)/../node/mapbox-geocode.js "$QUERY"`"
valid_response "$RESPONSE"
if [ "$VALID" -ne 0 ]; then
    echo "not ok: node/mapbox-geocode.js"
    CODE=1
else
    echo "ok: node/mapbox-geocode.js"
fi

# bash
RESPONSE="`bash $(dirname $0)/../bash/mapbox_geocode.sh "$QUERY"`"
valid_response "$RESPONSE"
if [ "$VALID" -ne 0 ]; then
    echo "not ok: bash/mapbox_geocode.sh"
    CODE=1
else
    echo "ok: bash/mapbox_geocode.sh"
fi

# ----- batch geocoding -----

SHUF="`which shuf || true`"
if [ -z "$SHUF" ]; then
    SHUF="`which gshuf`"
fi
OUT_DIR="$(dirname $0)/out"
mkdir -p $OUT_DIR || true
rm -f $OUT_DIR/* || true
SAMPLE_FILE="$(dirname $0)/sample_`date +"%s"`.txt"
$SHUF $(dirname $0)/sample.txt > $SAMPLE_FILE

# python
for PYTHONX in $PYTHONS; do
    $(dirname $0)/../batch_python/.virtualenv_${PYTHONX}/bin/python $(dirname $0)/../batch_python/mapbox_batch.py $SAMPLE_FILE $OUT_DIR
    valid_batch_response $SAMPLE_FILE $OUT_DIR
    if [ "$VALID" -ne 0 ]; then
        echo "not ok: batch_python/mapbox_batch.py using $PYTHONX"
        CODE=1
    else
        echo "ok: batch_python/mapbox_batch.py using $PYTHONX"
    fi
    rm $OUT_DIR/* || true
done

# node
node $(dirname $0)/../batch_node/mapbox-batch.js $SAMPLE_FILE $OUT_DIR
valid_batch_response $SAMPLE_FILE $OUT_DIR
if [ "$VALID" -ne 0 ]; then
    echo "not ok: batch_node/mapbox-batch.js"
    CODE=1
else
    echo "ok: batch_node/mapbox-batch.js"
fi
rm $OUT_DIR/* || true

# ----- teardown -----

# teardown
rm -rf $OUT_DIR $SAMPLE_FILE
rm -rf $(dirname $0)/../node/node_modules/
rm -rf $(dirname $0)/../batch_node/node_modules/
rm -rf $(dirname $0)/../python/.virtualenv_*/
rm -rf $(dirname $0)/../batch_python/.virtualenv_*/

exit $CODE
