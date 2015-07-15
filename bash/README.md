# bash

`mapbox_geocode.sh` takes a single query as a parameter and outputs the result.

## Requirements

- the `hexdump` utility (standard on OS X, Ubuntu, Debian & most other distributions)
- A Mapbox access token with geocoding capabilities

## Usage

```
MapboxAccessToken=YOUR_ACCESS_TOKEN ./mapbox_geocode.sh '1600 Pennsylvania Ave Washington, DC'
```