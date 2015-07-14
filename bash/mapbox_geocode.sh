# check for access token
if [ -z "$MAPBOX_ACCESS_TOKEN" ]; then
    echo "You must specify a valid MAPBOX_ACCESS_TOKEN environment variable"
    exit 1
fi

# escape query
QUERY="$(echo -ne "$1" | hexdump -v -e '/1 "%02x"' | sed 's/\(..\)/%\1/g')"

# send query
curl "http://api.tiles.mapbox.com/v4/geocode/mapbox.places-permanent/${QUERY}.json?access_token=${MAPBOX_ACCESS_TOKEN}"