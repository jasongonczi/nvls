# startup.sh
if [ "$INSTALL_STRIPE" = "true" ]; then
  composer require stripe/stripe-php
fi
