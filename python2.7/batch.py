# EXAMPLE BATCH GEOCODING SCRIPT
# ------------------------------
# Usage: python mapbox_batch.py input.txt /path/to/output_dir/
# Note: script requires that the environment variable MAPBOX_ACCESS_TOKEN be set

import __future__
import json, sys, urllib, os.path
import grequests

BATCH_SIZE = os.environ.get('MAPBOX_BATCH_SIZE', 50)
CONCURRENT_REQUESTS = os.environ.get('MAPBOX_CONCURRENT_REQUESTS', 5)
ACCESS_TOKEN = os.environ.get('MAPBOX_ACCESS_TOKEN', False)
if not ACCESS_TOKEN:
    print('environment variable MAPBOX_ACCESS_TOKEN must be set')
    sys.exit(1)

def chunks(queries):
    for i in xrange(0, len(queries), BATCH_SIZE):
        yield queries[i:(i + BATCH_SIZE)]

if __name__ == '__main__':
    output_path = os.path.abspath(sys.argv[2])
    if not os.path.exists(output_path):
        print('output path {} does not exist'.format(output_path))
        sys.exit(1)

    # read input file
    with open(sys.argv[1], 'r') as input_file:
        queries = input_file.readlines()

    # build requests
    reqs = []
    for chunk in chunks(queries):
        escaped_queries = ';'.join([urllib.quote_plus(s.strip()) for s in chunk])
        reqs.append(grequests.get('http://api.tiles.mapbox.com/v4/geocode/mapbox.places-permanent/{}.json?access_token={}'.format(escaped_queries, ACCESS_TOKEN)))

    # send requests, save responses
    for (i, response) in enumerate(grequests.imap(reqs, stream=False, size=CONCURRENT_REQUESTS)):
        if response.status_code == 200:
            print('- response received, saving to {}/{}.json'.format(output_path, i))
            with open('{}/{}.json'.format(output_path, i), 'w') as output_file:
                json.dump(response.json(), output_file, indent=4)
        else:
            print('# error')
