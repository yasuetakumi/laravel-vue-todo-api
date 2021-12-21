<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div>
        <h2 style="text-align: center">Report From Starter Kit</h2>
        <div>
            <p>Error Message : </p>
            <p>{{$messages['error']}}</p>
        </div>
        <div style="display: flex">
            <p>URL : </p>
            <p><a href="{{$messages['url']}}">{{$messages['url']}}</a></p>
        </div>
        <br>
        <p>Thank You</p>
    </div>
</body>
</html>
