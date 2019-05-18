# OnePlus-bbs-Bot

Automatic Signer/Drawer Bot. ver 1.3.1

Use for OnePlus bbs (CN).

Replace `cookie` with your bbs cookies.

## Usage

- Webpage:

PHP + nginx: `do.php?mode=[sign|draw|all]`

- Command:

Linux: `php do.php [sign|draw|all]`

Windows: `php.exe do.php [sign|draw|all]`

## Auto run

Windows: Task Scheduler `php.exe do.php [sign|draw|all]` Every day 12:00:00

Linux: Crontab `0	12 * * * php.exe do.php [sign|draw|all]`

It is not recommended to set it to 0:00 per day.

There is a high probability of execution failure. (Caused by network, bandwidth, server of bbs, etc.)

## TODO

- judge for sign success.
