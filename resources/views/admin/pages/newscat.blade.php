@extends("admin.main")

@section('content')

    <div class="container-fluid">
        <div class="category-tab"><!--category-tab-->
                <button class="btn btn-default" style="margin-top: 5px;"><a href="{{route('addnewscat')}}"><font color="black">Add a Category</font></a></button>

            <div class="tab-content">
               <br>
                <div class="col-lg-12">
                    <!-- Table for job listing -->
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Title</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($category as $item)

                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->title }}</td>
</td>
                                    <td>
                                        <!-- Edit and Delete buttons in the same row -->
                                        <a href="{{route('editnewscat',['id'=>$item->id])}}" class="btn btn-warning">Edit</a>
                                        <a href="javascript:void(0)" class="btn btn-danger"
                                           onclick="confirmAction({
                                               title: 'Delete News Category',
                                               message: 'Are you sure you want to delete this news category? This action cannot be undone.',
                                               confirmText: 'Yes, Delete',
                                               confirmClass: 'btn-danger',
                                               onConfirm: function() { window.location.href = '{{route('dellnewscat',['id'=>$item->id])}}'; }
                                           });">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div><!--/category-tab-->

        </div>
    </div>

@endsection

