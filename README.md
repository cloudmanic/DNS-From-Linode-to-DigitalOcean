# Migrate DNS From Linode To Digital Ocean

Quick and dirty script to migrate DNS records from (Linode)[https://www.linode.com] to (Digital Ocean)[https://www.digitalocean.com].

This is not my finest code ever written nor is it fully tested. It was designed to be used as a one time script to move my DNS from Linode To Digital Ocean

# Install

* Login to Digital Ocean and setup a personal access token. https://cloud.digitalocean.com/account/api/tokens

* In `app.php` replace Replace `$doAccessToken = "XXXXXXXXXXXXX";` with your new access token.

* Install Linode's CLI tool https://www.linode.com/docs/platform/api/linode-cli. (I got lazy and used this instead of building PHP API calls into the app). You know it is installed when you can run this `linode-cli domains list` and see your domains from the CLI.

* Install composer packages `composer install`

# Run

* Just run `php app.php` and watch your domains migrate over.