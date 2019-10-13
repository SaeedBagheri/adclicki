@extends('layouts.material.layout')
@section('header')
    @parent

    <title> لیست زیر مجموعه اجاره ای من </title>

@endsection
@section('content')


    <div class="card">


    </div>

    <div class="col-md-12 col-sm-12">


        <div class="card">

            <div class="card-block">

                <div class="clearfix">
                    <div class="float-right">
                        <h5> لیست زیر مجموعه اجاره ای من </h5>
                    </div>


                </div>
                <div class="my_table">
                    @include('layouts.material.admin.subcategory.table')
                </div>
            </div>


        </div>


        <script>
            $(document).ready(function () {

                $(document).on('click', '.pagination a', function (event) {
                    event.preventDefault();
                    $('#loader').show();
                    var page = $(this).attr('href');
                    fetch_data(page);


                });


            });

            function fetch_data(page) {

                $.ajax({
                    url: page,
                    success: function (data) {
                        $('.my_table').html(data);
                        $('#loader').hide();

                    },
                    error: function (error) {


                    }
                });
            }

        </script>





@stop