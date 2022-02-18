<!DOCTYPE html>
<html dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    {{-- <link rel="icon" type="image/png" sizes="16x16" href="https://scrcpybackend.herokuapp.com/css/assets/images/favicon.png"> --}}
    <title>Yougo | Error</title>
    <script>window.Laravel = { csrfToken: '{{csrf_token()}}'} </script>

    <style>
        *{
                transition: all 0.6s;
            }

            html {
                height: 100%;
            }

            body{
                font-family: 'Lato', sans-serif;
                color: #888;
                margin: 0;
            }

            #main{
                display: table;
                width: 100%;
                height: 100vh;
                text-align: center;
            }

            .fof{
                display: table-cell;
                vertical-align: middle;
            }

            .fof h1{
                font-size: 50px;
                display: inline-block;
                padding-right: 12px;
                animation: type .5s alternate infinite;
            }

            @keyframes type{
                from{box-shadow: inset -3px 0px 0px #888;}
                to{box-shadow: inset -3px 0px 0px transparent;}
            }
    </style>
</head>

<body>

   <div id="main">
    <div class="fof">
            <h1>Error 404</h1>
    </div>
    </div>

</body>




</html>