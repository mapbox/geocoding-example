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
    """
    Submit a geocoding query to Mapbox's geocoder.
    
    Args:
        mapbox_access_token (str): valid Mapbox access token with geocoding permissions
        query (str): input text to geocode
    """
    resp = urlopen('https://api.tiles.mapbox.com/v4/geocode/mapbox.places/{query}.json?access_token={token}'.format(query=quote_plus(query), token=mapbox_access_token))
    return json.loads(resp.read().decode('utf-8'))

if __name__ == '__main__':
    token = os.environ.get('MapboxAccessToken', False)
    if not token:
        print('environment variable MapboxAccessToken must be set')
        sys.exit(1)

    # geocode
    result = geocode(token, sys.argv[1])

    # print result
    print(json.dumps(result, indent=2))
