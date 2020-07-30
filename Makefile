.PHONY: no3DS

install:
	composer install

no3DS:
	php no3DS/no3DS.php

refund:
	php no3DS/refund.php

no3DSBut3DSTriggers:
	php no3DS/3DSTriggers.php

forwardWebhooks:
	export $(grep -v '^#' .env | xargs) && stripe listen --forward-to localhost:8085/webhook

3DSWebhooks:
	php -S 127.0.0.1:8085 3DS/3DSWebhooks.php

trigger3DS:
	php with3DS/3DSTriggers.php
