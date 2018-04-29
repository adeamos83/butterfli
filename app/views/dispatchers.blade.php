@extends('layout')

@section('content')

    <div><a id="add_balance_pop_up" class="btn-sm" data-toggle="modal" href="#add_balance_pop_up_responsive" style="display:none;"></a></div>
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
<div align="center" id="loadingimage" style="display:none;"><img src="<?= asset_url() . '/web/img/preloader.gif' ?>" style="z-index: 9999;position: fixed;left: 565px;" /></div>
<div class="box box-info tbl-box">
    <div align="left" id="paglink"><?php echo $Dispatchers->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
    <table class="table table-bordered">
        <tbody><tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Transportation Provider</th>
                <th>Is Admin</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($Dispatchers as $Disp) { ?>
                <tr>
                    <td><a href="/admin/dispatcher/profile/<?= $Disp->id ?>"><?= $Disp->id ?></a></td>
                    <td><?php echo $Disp->contact_name; ?> </td>
                    <td><?= $Disp->email ?></td>
                    <td><?php
                            if($Disp->transportation_provider_id!=null){
                                echo $Disp->transportation_company;
                            } else{
                                echo "NA";
                            }
                            ?>
                    </td>
                    <td>
                        <?php
                        if ($Disp->is_admin == 1) {
                            echo "<span class='badge bg-green'>Yes</span>";
                        } else {
                            echo "<span class='badge bg-red'>No</span>";
                        }
                        ?>
                    </td>
                    <td>
                    <?php
                        if ($Disp->is_active == 1) {
                            echo "<span class='badge bg-green'>Approved</span>";
                        } else {
                            echo "<span class='badge bg-red'>Pending</span>";
                        }
                        ?>
                    </td>
                    <td>

                        <div class="dropdown">
                            <button class="btn btn-flat btn-info dropdown-toggle" type="button" id="dropdownMenu1" name="action" data-toggle="dropdown">
                                Actions
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                <?php if ($Disp->is_active == 0) { ?>
                                            <li role="presentation"><a role="menuitem" id="approve" tabindex="-1" href="{{ URL::Route('AdminDispatcherApprove', $Disp->id) }}">Approve</a></li>
                                <?php } else { ?>
                                            <li role="presentation"><a role="menuitem" id="decline" tabindex="-1" href="{{ URL::Route('AdminDispatcherDecline', $Disp->id) }}">Decline</a></li>
                                <?php }
                                      if ($Disp->is_admin == 0) { ?>
                                            <li role="presentation"><a role="menuitem" id="admindispatcher" tabindex="-1" href="{{ URL::Route('MakeDispatcherAdmin', $Disp->id) }}">Make Dispatcher Admin</a></li>
                                <?php }else{ ?>
                                            <li role="presentation"><a role="menuitem" id="admindispatcher" tabindex="-1" href="{{ URL::Route('RemoveDispatcherAdmin', $Disp->id) }}">Remove Dispatcher Admin</a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody></table>
    <div align="left" id="paglink"><?php echo $Dispatchers->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
</div>

<script type="text/javascript">
</script>

@stop