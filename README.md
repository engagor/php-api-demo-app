# engage-api-demo

[![Build Status][ico-travis]][link-travis]


This is a demo application for the Clarabridge Engage API. You can use this to start off a new project using our API, or use it as an example.


## Usage


Clone this repo:

```sh
$ git clone https://github.com/engagor/engage-api-demo
```

Change directory to the repository

```sh
$ cd engage-api-demo
```

Install composer dependencies

```sh
$ composer install
```

Run the built-in webserver

```sh
$ php -S {your-local-ip-here}:8000 -t public/
```

You can now visit [localhost:8000](http://localhost:8000) in your browser to see the result.


## Integrate with Engage

Copy the `.env.dist` file to `.env`

```sh
cp .env.dist .env
```

Find the `CLIENT_ID` and `CLIENT_SECRET` for your application in your [list of applications](https://developers.engagor.com/applications), and put them in the `.env` file.

Make your local webserver available to Engage (you'll need [localtunnel](https://localtunnel.me/))

```sh
$ lt --port 8000 --subdomain {your-subdomain-here} --local-host {your-local-ip-here}
```

Now, in Engage, you can setup an [automation rule](https://help.engagor.com/customer/portal/articles/1628555#Post) that forwards incoming private messages to the url acquired by running the previous command, e.g.

```
https://{your-subdomain-here}.localtunnel.me/webhooks
```

Make sure thet automation rule uses the `POST` method and has this body:

```
[mention]
```


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.


## Security

If you discover any security related issues, please email dev@engagor.com instead of using the issue tracker.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-travis]: https://img.shields.io/travis/engagor/engage-api-demo/master.svg?style=flat-square
[link-travis]: https://travis-ci.org/engagor/engage-api-demo
