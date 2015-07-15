# Python 2/3 - Mapbox Geocoder

`mapbox_geocode.py` takes a single query as a parameter and outputs the result.

## Requirements

- Python 2/3
- A Mapbox access token with geocoding capabilities

## Usage

Command line:
```
MapboxAccessToken=YOUR_ACCESS_TOKEN python ./mapbox_geocode.py '1600 Pennsylvania Ave Washington, DC'
```

Programmatic:
```
>>> from mapbox_geocode import geocode
>>> geocode('YOUR_ACCESS_TOKEN', '1600 Pennsylvania Ave Washington, DC')
{u'attribution': u'\xa9 2015 Mapbox and its suppliers. All rights reserved. Use of this data is subject to the Mapbox Terms of Service. (https://www.mapbox.com/about/maps/)', u'query': [u'1600', u'pennsylvania', u'ave', u'washington', u'dc'], u'type': u'FeatureCollection', u'features': [{u'center': [-77.036698, 38.897102], u'geometry': {u'type': u'Point', u'coordinates': [-77.036698, 38.897102]}, u'text': u'Pennsylvania Ave NW', u'properties': {}, u'bbox': [-77.05781199999998, 38.89252299999999, -77.01844799999999, [...]
```

