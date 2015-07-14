# Python 2/3

`mapbox_batch.py` reads queries from an input text file, one per line, and constructs parallelized batch queries.

Results are stored in an output directory as JSON files.

## Requirements

- [gevent 1.1a2 or later](https://pypi.python.org/pypi/gevent)
- A Mapbox access token with batch geocoding capabilities (email sales@mapbox.com)

## Installation

`pip install -r requirements.txt`

## Usage

```
MAPBOX_ACCESS_TOKEN=__your access token__ python mapbox_batch.py input_file.txt /path/to/output/directory
```
