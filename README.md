ruTorrent RPC
=========

A file that allows secure API calls with ruTorrent.

This file is necessary because ruTorrent has no API to securely return torrent data. If I returned data the same way that ruTorrent fetches it, anyone could control anyone else's server using my app, which relies on the password protection of the client (XMLRPC has none).
