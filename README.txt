SM CONSTRUCTION WEBSITE
=======================

This folder is a complete static website and can be uploaded to most website hosts.

MAIN FILE
- index.html

PAGES
- Home
- Bathrooms
- Kitchens
- Basements
- Services
- Gallery
- About
- Contact / Estimate Request
- SEO bathroom pages for Hamilton, Stoney Creek, Ancaster, Burlington, Grimsby and St. Catharines

CONTACT DETAILS CURRENTLY USED
- Steve Mes
- Phone: 289-799-9644
- Email: sales@sm-construction.com
- Domain: www.sm-construction.com

HOW TO UPLOAD
1. Unzip the package.
2. Upload ALL files and the entire assets folder into the public/root folder of the hosting account.
3. Make sure index.html is at the root.
4. Test navigation, Call, Text and Estimate buttons on a phone.
5. If the domain is not already pointing to the host, connect sm-construction.com using the host's DNS instructions.

RESEND FORM SETUP
The estimate form posts to send-estimate.php. Hosting requires PHP 7.4+ with
cURL enabled (use a currently supported PHP version offered by your host).

1. Upload the website files, including send-estimate.php, into public_html.
2. In your hosting File Manager, go ONE LEVEL ABOVE public_html.
3. Create sm-resend-config.php there using sm-resend-config.example.php as a template.
   Never put the real configuration or API key inside public_html or assets.
4. Set api_key to your Resend sending API key. Set from to an address on a
   domain you have verified in Resend, e.g. SM Construction <estimates@your-domain.com>.
5. Confirm to is the inbox that should receive requests (currently sales@sm-construction.com).
6. Open the live contact page, send a test request, and check the receiving inbox
   and Resend dashboard. A success message means Resend accepted the email;
   final delivery should be checked in Resend. Test replying to the visitor too.

Expected host layout:
  account-folder/sm-resend-config.php   (private; contains your key)
  account-folder/public_html/send-estimate.php
  account-folder/public_html/contact.html
  account-folder/public_html/assets/...

Email is optional unless the visitor selects Email as their preferred contact.
The endpoint validates fields, adds the visitor's email as Reply-To, includes a
honeypot and limits each IP to five attempts per ten minutes using the host's
writable temporary directory. Identical requests are deduplicated by Resend
for 24 hours. Failed submissions keep the visitor's form entries for retry.
If sending fails, confirm PHP/cURL, the private config location, verified sender,
API key permissions and the host's outbound HTTPS access. Server logs contain
only the provider HTTP status, not the API key or visitor details.
API reference: https://resend.com/docs/api-reference/emails/send-email

PHOTOS
The package currently uses imagery taken from the SM Construction proposal artwork supplied in ChatGPT.
To add more real project photos, place them in /assets and add them to gallery.html.

SEO
- Titles and meta descriptions are included.
- LocalBusiness structured data is included.
- sitemap.xml and robots.txt are included.
- Local bathroom renovation landing pages are included.

BRAND
The website uses the same dark navy, gold and white visual language as the SM Construction proposal design.


GALLERY UPDATE
- Real SM Construction bathroom and basement project photos are already included.
- Gallery filtering and click-to-enlarge lightbox are already set up.
- Homepage, Bathroom page and Basement page now show real project imagery.
- No photo setup is required before upload.

FOR STEVE'S SON
Upload the CONTENTS of the sm-construction-website folder to the web root/public_html.
Keep the assets folder structure exactly as supplied.
Complete RESEND FORM SETUP above before using the live estimate form.
