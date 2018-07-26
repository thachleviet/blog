
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
                <form  class="form-inline" action="{{route('submit_install')}}" >
                    <div class="form-group" class="col-lg-12">
                        <input type="text" class="form-control" id="shop" placeholder="Enter host name" name="shop">
                        <button type="submit" class="btn btn-default">Submit</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- /.row -->
    </div>
@endsection