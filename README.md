

## About YouGo 

YouGo is an all-encompassing dispatch application for various dispatch companies. 
Here is the backend development of the YouGo project. This project is splitted into 4 different aspects, such as:

- User Part.
- Partner Part.
- Rider Part.
- Admin Part.


> ## Tech Stack
The stacks used for the project include:

| <b><u>Technology</u></b> | 
| :---         | 
| **` PHP`** | 
| **`Laravel`** | 
| **`MYSQL`** | 


## Project Setup

### Setting up your workspace

Before running this app, locally make sure you have the following software installed:

-   XAMPP or it's equivalent
-   Composer

Now, follow this steps to start contributing:

```
 git clone https://github.com/<YOUR-GITHUB-USERNAME>/yougo-api.git
```

2. Run `cd yougo-api`

3. Run `composer install`

4. Run `php artisan key:generate --show` to retrieve a base64 encoded string for Laravel's `APP_KEY` in `.env`

5. Run `php artisan serve` from your terminal and the app will be running on `http://127.0.0.1:8000/`
