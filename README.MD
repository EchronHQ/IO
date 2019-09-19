## PHP7 IO library

#Clients
FTP                     Todo
SFTP                    Done
HTTP(S)                 Done
- with authentication   Todo
Dropbox                 Done
OneDrive                Todo
Amazon S3               Todo


#TODO: 
Add client possibility functions like "canChangeRemoteDate" or "canPush" ?
Lastmodified timezone
Store filestat locally/cached 

####Transaction result (push/pull)

For instance an object with the status (succeeded), transferred bytes, ...
For some implementations it's possible to know that, when it's unknown set status on null
When doing lazy transfer, show that in the transaction result