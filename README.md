# Magento 2 Newsletter Ajax
Magento 2 Newsletter Ajax allows your customers to subscribe to the newsletter without refreshing the page. It also returns JSON data to developers who want a custom subscription process.


## How to Install

Download to the project root `app/code/SS/NewsletterAjax` directory.

Run the following commands in the project root:

### Developer Mode
```
php bin/magento module:enable SS_NewsletterAjax
php bin/magento setup:upgrade
```

### Production Mode
```
php bin/magento module:enable SS_NewsletterAjax
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## How to Use

By installing the module you will now be able to make AJAX requests to the newsletter subscription endpoint `https://yourdomain.com/newsletter/subscriber/new`
and get a JSON response back with a `status` and `message`. This will not interfere with current newsletter subscription forms as
the module checks the HTTP request header and bases the response on that value. This ensures compatiblity with the core Magento 2 functionality and
other modules extending the newsletter subscription.

You can use this snippet as a starting point if you want to run a custom process on the front-end:

```
<script>
    require([
        'jquery'
    ], function ($) {
        $(function () {
            let subscribeForm = $('.form.subscribe');

            $(subscribeForm).on('submit', function(e) {
                e.preventDefault();
                let email = $('#newsletter').val();

                if ($(subscribeForm).valid()) {
                    $.ajax({
                        url: 'newsletter/subscriber/new/',
                        type: 'POST',
                        data: {
                            'email' : email
                        },
                        dataType: 'json',
                        showLoader: true,
                        complete: function(data, status) {
                            let response = JSON.parse(data.responseText);
                            // Run your custom process using the response data
                        }
                    });
                }
            });
        });
    });
</script>
```



## User Guide

Login to your Magento 2 admin and select "Stores" from the sidebar. Under "Settings" choose "Configuration". From there you can select
"SS" from the sidebar and see the Newsletter Ajax options. Enabling this will override the default newsletter subscription input in the footer
and will change it to use AJAX to make a background request and will launch a modal with the results. 

