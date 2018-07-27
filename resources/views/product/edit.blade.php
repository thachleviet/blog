@extends('layouts.master')
@section('content')

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Dashboard</h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->

        <!-- /.row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        Update Product
                    </div>
                    <!-- /.panel-heading -->
                    <div class="panel-body" >
                        <form method="post" action="{{route('product.submit_edit', $object->id)}}">
                            {!! csrf_field() !!}
                            {{ method_field('PUT') }}
                            <div class="form-group">
                                <label for="email">Name  product:</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{$object->title}}">
                                <input type="hidden" name="id" id="id" value="{{$object->id}}">
                            </div>

                            <button type="submit" class="btn btn-success">Save</button>
                            <a href="{{route('product')}}"  type="submit" class="btn btn-danger">Cancel</a>
                        </form>

                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel -->
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
    </div>
@endsection