I am trying to build an ERP for the Jesuits. Now, I want to build both mobile app for Android iOS and also web. Which means I need to build rest API. I want to use secure authentication, like Google auth etc with least friction but should work on all platforms. 
Mobile app can be done with flutter or other ways too, but not native way. 

The application will contain database server application, role based authentication, generate pdf for print (almost 100 pages that contains all details of all members in a specific format). I have used react but wasn't happy with it. I liked sveltekit. Comfrotable with php though not tried Laravel, but I can pick it up. Php servers are cheap too. I will have to host if possible on AWS or other cloud so that I will have data backup. 

This is like ERP. So it contains all details like all their personal details, wherr they have been etc. Also, there are many communities that they will be assigned to. Every year, they bring out a catalogue or prospectus containing details of where each member is present. It has a proper format. An entire pdf should be able to be generated.

Tell me how shall I go about it.
Ok I have decided to go with railway and cloudflare R2.
Storage: Cloudfare R2
Auth: Google signin and phone signin(firebase) with Laravel's RBAC
Mobile APP: Flutter
Database: Postgres
API Server: Laravel
Hosting: Railway

Now, you need to provide detailed instruction on setting up the project
By the way, there are multiple projects here
There is an REST API server in Laravel
There is admin and user interface in Laravel
There is mobile app in flutter
But I don't want to setup multiple github repos
May be, we can use orphan branches and setup the laravel and flutter projects

I think it is better to have only 2 orphan branches and their dev branches: one for mobile app and another for web, api server

Web should contain both admin part and user part
But only user part should be accessible to flutter app as well
