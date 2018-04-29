@extends('layout')

@section('content')

    <style>
        .modal-backdrop {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: #000000;
        }

        .modal-backdrop.fade {
            opacity: 0;
        }

        .modal-backdrop,
        .modal-backdrop.fade.in {
            opacity: 0;
            filter: alpha(opacity=80);
        }
        .modal-title {
            color: #e8ecec;
        }
    </style>
    @if(Session::has('error'))
        <div class="alert alert-danger">
            <b>{{ Session::get('error') }}</b>
        </div>
    @endif
    @if(Session::has('success'))
        <div class="alert alert-success">
            <b>{{ Session::get('success') }}</b>
        </div>
    @endif
    <div align="center" id="loadingimage" style="display:none;"><img src="<?= asset_url() . '/web/img/preloader.gif' ?>" style="z-index: 9999;position: fixed;left: 565px;" /></div>
    <a href="{{route('AddNewTransportationProvider')}}">
        <div class="btn btn-primary" >
            <strong style="font-size:15px;text-align: center" target="_blank">Add New Transportation Provider</strong>
        </div>
    </a>
    <a href="{{route('RatingTransportationProvider')}}">
        <div class="btn btn-primary" style="margin-left: 686px;">
            <strong style="font-size:15px;text-align: center" target="_blank">Enrolled TP's Rating</strong>
        </div>
    </a>
    <br/><br/>
<div class="box box-info tbl-box">
    <div align="left" id="paglink"><?php echo $transportationproviders->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
    <table class="table table-bordered">
        <tbody><tr>
                <th>ID</th>
                <th>Email</th>
                <th>Company</th>
                <th>Phone</th>
            </tr>

            <?php foreach ($transportationproviders as $transportationprovider) { ?>
                <tr>
                    <td><a href="/admin/tp/profile/<?= $transportationprovider->id ?>"><?= $transportationprovider->id ?></a></td>
                    <td><?= $transportationprovider->email ?></td>
                    <td><?= $transportationprovider->company ?></td>
                    <td><?= $transportationprovider->phone ?></td>
                </tr>
            <?php } ?>
        </tbody></table>
    <div align="left" id="paglink"><?php echo $transportationproviders->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
</div>
@stop