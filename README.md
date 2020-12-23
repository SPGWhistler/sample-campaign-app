## Sample Campaign Promo App

This is a sample app I built using Laravel as the php framework, SwiperJS for the gui, and some vanilla js to tie it all together.

It demonstrates fetching data from the https://api.digitalmedia.hhs.gov/ feed, processing it a little bit, and finding preview images for each item.

## Installing

To run this locally:
- First, make sure you have [Docker Desktop](https://www.docker.com/products/docker-desktop) installed.
- Then simply clone the repo, cd into the directory, and then run:
```./vendor/bin/sail up```

## Notes and Software used in this demo

I did not write all of the code in this demo. As stated above, I started with the Laravel framework, using their setup script to host the app in a docker container. For the UI, I pulled in SwiperJS to make the carousel easier to build. For styles, I started with normalize.css, added swiperjs' css on top of that, then wrote the additional styling by hand.

I am not using any precompiling or transpiling for the frontend code, just to make things a bit easier.

## License

The [Laravel framework](https://laravel.com/) and [SwiperJS](https://swiperjs.com/) are open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
