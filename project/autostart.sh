sudo -u www bash -c '#!/bin/bash

url="网站域名/atouch.php?token=xxx"
curl -s "$url"
exit 0'