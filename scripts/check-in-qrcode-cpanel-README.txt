Check-in QR email (CID) — file bundle for cPanel
=================================================

This ZIP contains the four application files that embed the check-in QR in
approval emails using an inline image (CID), which displays correctly in
Gmail, Outlook, and most other clients.

Contents (paths relative to Laravel project root)
-------------------------------------------------
  app/Mail/RsvpDecisionMail.php
  app/Services/MailTemplateRenderer.php
  app/Services/RsvpCheckInQrService.php
  resources/views/mail/raw-html.blade.php

How to install on cPanel
------------------------
1. Upload the ZIP via File Manager (or FTP).

2. In File Manager, open the folder that is your Laravel ROOT — the same
   directory that contains the file named "artisan" (not public/ by itself).

3. Extract the ZIP here and choose "Overwrite" when asked.

   That replaces only the four files above; nothing else is touched.

4. Clear compiled views (SSH / Terminal, from project root):

     php artisan view:clear

Regenerating this bundle
------------------------
On your computer, from a full git clone of this project:

     ./scripts/export-check-in-qrcode-cpanel-zip.sh [COMMIT_OR_TAG]

Default COMMIT_OR_TAG is HEAD (current checkout). Example:

     ./scripts/export-check-in-qrcode-cpanel-zip.sh c35a691

No Composer or npm changes are required for this bundle.
