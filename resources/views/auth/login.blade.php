<!DOCTYPE html>
<html dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="https://scrcpybackend.herokuapp.com/css/assets/images/favicon.png">
    <title>Admin</title>
    <script>window.Laravel = { csrfToken: '{{csrf_token()}}'} </script>
    <link href="https://scrcpybackend.herokuapp.com/css/style.min.css" rel="stylesheet">
</head>

<body>

   <div id="app">
       <p>No no no</p>
   </div>

</body>


<script src=" {{secure_asset('js/app.js')}} "></script>
<script src="{{secure_asset("js/assets/libs/jquery/dist/jquery.min.js")}} "></script>
<script src="{{secure_asset("js/assets/libs/popper.js/dist/umd/popper.min.js")}}"></script>
<script src="{{secure_asset("js/assets/libs/bootstrap/dist/js/bootstrap.min.js")}}"></script>
<script src="{{secure_asset("js/dist/js/app-style-switcher.js")}}"></script>
<script src="{{secure_asset("js/dist/js/feather.min.js")}}"></script>
<script src="{{secure_asset("js/assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js")}}"></script>
<script src="{{secure_asset("js/assets/extra-libs/sparkline/sparkline.js")}}"></script>
<script src="{{secure_asset("js/dist/js/sidebarmenu.js")}}"></script>
<script src="{{secure_asset("js/dist/js/custom.min.js")}}"></script>
{{-- <script src="{{asset("js/assets/libs/jquery/dist/jquery.min.js")}} "></script>
<script src="{{asset("js/assets/libs/popper.js/dist/umd/popper.min.js")}} "></script> --}}
<script src="{{secure_asset("js/assets/libs/bootstrap/dist/js/bootstrap.min.js")}} "></script>


</html>
