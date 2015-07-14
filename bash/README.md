# bash

`mapbox_geocode.sh` takes a single query as a parameter and outputs the result.

## Requirements

- the `hexdump` utility (standard on OS X, Ubuntu, Debian & most other distributions)
- A Mapbox access token with batch geocoding capabilities (email sales@mapbox.com)

## Usage

```
MAPBOX_ACCESS_TOKEN=__your access token__ ./mapbox_geocode.sh '1600 Pennsylvania Ave Washington, DC'
```