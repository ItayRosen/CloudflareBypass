# CloudflareBypass
This is a simple & fast PHP class that bypasses Cloudflare's UAC (browser integrity check mechanism).

# Usage
1. Include the class ( require 'cf-bypass.php'; )
2. Initiate the class ( $cfb = new cfBypass; )
3. Set the target url ( $cfb -> url = 'https://website.com'; )
4. Get the protected content ( echo $cfb -> bypass(); )
