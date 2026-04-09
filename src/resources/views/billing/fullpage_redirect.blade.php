<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <base target="_top">
        <meta name="shopify-api-key" content="{{ config('shopify-app.api_key') }}" />
        <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>

        <title>Redirecting...</title>

        <style type="text/css">
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
                font-family: -apple-system, BlinkMacSystemFont, San Francisco, Roboto,
                    Segoe UI, Helvetica Neue, sans-serif;
                background-color: #f4f6f8;
            }
            .redirect-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 100vh;
                text-align: center;
            }
            .redirect-message {
                color: #637381;
                font-size: 1.6rem;
                margin-bottom: 2rem;
            }
            .redirect-button {
                background-color: #008060;
                color: white;
                border: none;
                border-radius: 4px;
                padding: 1.2rem 2.4rem;
                font-size: 1.4rem;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                transition: background-color 0.2s ease;
            }
            .redirect-button:hover {
                background-color: #006e52;
            }
            .hidden {
                display: none;
            }
        </style>

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
                let redirectUrl = "{!! $url !!}";
                let redirectButton = document.getElementById('redirect-button');
                let redirectMessage = document.getElementById('redirect-message');

                // Attempt automatic redirect
                if (window.top === window.self) {
                    window.top.location.href = redirectUrl;
                } else {
                    open(redirectUrl, '_top');
                }

                // Show fallback button after a short delay in case automatic redirect is blocked
                setTimeout(function() {
                    redirectMessage.textContent = 'If you are not redirected automatically, please click the button below:';
                    redirectButton.classList.remove('hidden');
                }, 2000);

                // Handle manual redirect click
                redirectButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (window.top === window.self) {
                        window.top.location.href = redirectUrl;
                    } else {
                        open(redirectUrl, '_top');
                    }
                });
            });
        </script>
    </head>
    <body>
        <div class="redirect-container">
            <p id="redirect-message" class="redirect-message">Redirecting...</p>
            <a href="{!! $url !!}" id="redirect-button" class="redirect-button hidden">Continue to Shopify</a>
        </div>
    </body>
</html>
