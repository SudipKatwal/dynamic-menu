<!DOCTYPE>
<html>
<head>
    <title>Dynamic Menu</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <div class="row">
            <h1>Create New Menu</h1>
            <form class="well" action="{{route('menus.store')}}" method="post">
                {{csrf_field()}}
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" class="form-control" id="name" placeholder="Menu Name" required>
                </div>
                <div class="form-group">
                    <label for="parent">Parents</label>
                    <select id="parent" name="parent_id" class="form-control">
                        <option value="0">Select Parent</option>
                        @if(count($menus))
                            @foreach($menus as $key=>$menu)
                                <option value="{{$menu->id}}">{{$menu->name}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group">
                    <label for="position">Position After</label>
                    <select id="position" name="position" class="form-control">
                        <option value="0">Select Position</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-default">Submit</button>
                </div>
            </form>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script>
    $(document).on('change','#parent',function () {
        var url = '{{route('menus.show',':parent-id')}}';
        url = url.replace(':parent-id',$(this).val());
        $.ajax({
            type:'get',
            url:url,
            data:{},
            success:function (html) {
                $('#position').html('');
                $('#position').append(html);
            }
        });
    })
</script>

</body>
</html>