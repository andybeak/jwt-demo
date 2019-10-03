# JWT demo

This is an example of using the "[JWT Framework](https://github.com/web-token/jwt-doc)" library

See the documentation at https://web-token.spomky-labs.com

## Running the demo

Install the dependencies with `composer install`

In the `public` directory run the command `php -S localhost:8000 index.php`

Note that if you do not specify the `index.php` file when running the local service the routing will not work and
when you try to check the token (or logout) PHP will return a 404 error.

## Creating PEM

If you want to create your own PEM file use the steps below.

In step 2 call your file "key", or otherwise change the parameter you pass in line three

```
cd jwk
ssh-keygen -t rsa -b 4096
openssl rsa -in key -outform pem > cert.pem
```
