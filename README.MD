# Echron PHP IO library

[![Latest Stable Version](https://img.shields.io/packagist/v/echron/io.svg)](https://packagist.org/packages/echron/io)

## About

[Echron](https://echron.com) PHP IO library offers a set io clients

Install the latest version with

```bash
composer require echron/io
```

# Clients

| Client       | Usage                                                                                    |
|--------------|------------------------------------------------------------------------------------------|
| AWS S3       | Read and write from [Amazon Cloud Object Storage (S3)](https://aws.amazon.com/pm/serv-s) |
| Dropbox      | Dropbox Client                                                                           |
| FTP          | FTP client                                                                               |
| SFTP         | SFTP Client                                                                              |
| Google Drive | Google Drive client                                                                      |
| Http         | Http client, can for example be used to download PDF files                               |
| Memory       |                                                                                          |
| Bridge       | Acts as bridge between 2 clients making it easy to transfer from 1 storage to another    |

## TODO:

- Add client possibility functions like "canChangeRemoteDate" or "canPush" ?
- Lastmodified timezone
- Store filestat locally/cached

#### Transaction result (push/pull)

- For instance an object with the status (succeeded), transferred bytes, ...
- For some implementations it's possible to know that, when it's unknown set status on null
- When doing lazy transfer, show that in the transaction result

# check outdated

```bash
docker run -it -v ${PWD}:/app -w /app --rm attlaz/php:8.1 composer outdated -D
docker run -it -v ${PWD}:/app -w /app --rm attlaz/php:8.1 composer update
```

# Run PHPStan

```bash
docker run -it -v .:/app -w /app --rm attlaz/php:8.1 composer run-script phpstan
```
