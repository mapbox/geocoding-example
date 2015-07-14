import __future__
import os, sys, json
try:
    # python 3
    from urllib.request import urlopen as urlopen
    from urllib.parse import quote_plus as quote_plus
except:
    # python 2
    from urllib import quote_plus as quote_plus
    from urllib2 import urlopen as urlopen

def geocode(mapbox_access_token, query):
    """Submit a geocoding query to Mapbox's permanent geocoding endpoint."""
    resp = urlopen('http://api.tiles.mapbox.com/v4/geocode/mapbox.places-permanent/{query}.json?access_token={token}'.format(query=quote_plus(query), token=mapbox_access_token))
    return json.loads(resp.read().decode('utf-8'))

if __name__ == '__main__':
    MAPBOX_ACCESS_TOKEN = os.environ.get('MAPBOX_ACCESS_TOKEN', False)
    if not MAPBOX_ACCESS_TOKEN:
        print('environment variable MAPBOX_ACCESS_TOKEN must be set')
        sys.exit(1)

    # geocode
    result = geocode(MAPBOX_ACCESS_TOKEN, quote_plus(sys.argv[1]))

    # print result
    print(json.dumps(result, indent=2))