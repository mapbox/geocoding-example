# check for access token
if [ -z "$MapboxAccessToken" ]; then
    echo "You must specify a valid MapboxAccessToken environment variable"
    exit 1
fi

# escape query
QUERY="$(echo -ne "$1" | hexdump -v -e '/1 "%02x"' | sed 's/\(..\)/%\1/g')"

# send query
curl "http://api.tiles.mapbox.com/v4/geocode/mapbox.places-permanent/${QUERY}.json?access_token=${MapboxAccessToken}"