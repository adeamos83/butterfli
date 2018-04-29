@extends('layout')

@section('content')

<!--<div class="row">
    <div class="col-md-12 col-sm-12">
        <a id="addpro" href="{{ URL::Route('AdminProviderAdd') }}"><button class="btn btn-flat btn-block btn-info" type="button">Add Provider</button></a>
        <br/>
    </div>
</div>-->
<div class="col-md-6 col-sm-12">
    <div class="box box-danger">
        <form method="get" action="{{ URL::Route('/admin/sortpv') }}">
            <div class="box-header">
                <h3 class="box-title">Sort</h3>
            </div>
            <div class="box-body row">
                <div class="col-md-6 col-sm-12">
                    <select class="form-control" id="sortdrop" name="type">
                        <option value="provid" <?php
                        if (isset($_GET['type']) && $_GET['type'] == 'provid') {
                            echo 'selected="selected"';
                        }
                        ?> id="provid">{{ trans('customize.Provider');}} ID</option>
                        <option value="pvname" <?php
                        if (isset($_GET['type']) && $_GET['type'] == 'pvname') {
                            echo 'selected="selected"';
                        }
                        ?> id="pvname">{{ trans('customize.Provider'); }} Name</option>
                        <option value="pvemail" <?php
                        if (isset($_GET['type']) && $_GET['type'] == 'pvemail') {
                            echo 'selected="selected"';
                        }
                        ?> id="pvemail">{{ trans('customize.Provider'); }} Email</option>
                        <option value="pvaddress" <?php
                        if (isset($_GET['type']) && $_GET['type'] == 'pvaddress') {
                            echo 'selected="selected"';
                        }
                        ?>  id="pvaddress">{{ trans('customize.Provider'); }} Address</option>
                    </select>
                    <br>
                </div>
                <div class="col-md-6 col-sm-12">
                    <select class="form-control" id="sortdroporder" name="valu">
                        <option value="asc" <?php
                        if (isset($_GET['valu']) && $_GET['valu'] == 'asc') {
                            echo 'selected="selected"';
                        }
                        ?> selected id="asc">Ascending</option>
                        <option value="desc" <?php
                        if (isset($_GET['valu']) && $_GET['valu'] == 'desc') {
                            echo 'selected="selected"';
                        }
                        ?> id="desc">Descending</option>
                    </select>
                    <br>
                </div>
            </div>
            <div class="box-footer">    
                <button type="submit" id="btnsort" class="btn btn-flat btn-block btn-success">Sort</button>
            </div>
        </form>
    </div>
</div>

<div class="col-md-6 col-sm-12">

    <div class="box box-danger">

        <form method="get" action="{{ URL::Route('/admin/searchpv') }}">
            <div class="box-header">
                <h3 class="box-title">Filter</h3>
            </div>
            <div class="box-body row">

                <div class="col-md-6 col-sm-12">
                    <select class="form-control" id="sortdrop" name="type">
                        <option value="provid" <?php
                        if (isset($_GET['type']) && $_GET['type'] == 'provid') {
                            echo 'selected="selected"';
                        }
                        ?> id="provid"> {{ trans('customize.Provider'); }} ID</option>
                        <option value="pvname" <?php
                        if (isset($_GET['type']) && $_GET['type'] == 'pvname') {
                            echo 'selected="selected"';
                        }
                        ?> id="pvname">{{ trans('customize.Provider'); }} Name</option>
                        <option value="pvemail" <?php
                        if (isset($_GET['type']) && $_GET['type'] == 'pvemail') {
                            echo 'selected="selected"';
                        }
                        ?> id="pvemail">{{ trans('customize.Provider'); }} Email</option>
                    </select>
                    <br>
                </div>
                <div class="col-md-6 col-sm-12">
                    <input class="form-control" type="text" name="valu" value="<?php
                    if (Session::has('valu')) {
                        echo Session::get('valu');
                    }
                    ?>" id="insearch" placeholder="keyword"/>
                    <br>
                </div>

            </div>

            <div class="box-footer">

                <button type="submit" id="btnsearch" class="btn btn-flat btn-block btn-success">Search</button>


            </div>
        </form>

    </div>
</div>

<div class="col-md-12 col-sm-12">
    <?php if (Session::get('che')) { ?>
        <a id="providers" href="{{ URL::Route('AdminProviders') }}"><button class="col-md-12 col-sm-12 btn btn-warning" type="button">All {{ trans('customize.Provider');}}s</button></a><br/>
    <?php } else { ?>
        <a id="currently" href="{{ URL::Route('AdminProviderCurrent') }}"><button class="col-md-12 col-sm-12 btn btn-warning"  type="button">Currently Providing</button></a><br/>
    <?php } ?>
    <br><br>
</div>

<form role="form" method="get" action="{{ URL::Route('DownloadDriverReport') }}">
    <div class="box-footer">
        <button type="submit" name="submit" class="btn btn-primary" value="Download_Report">Download Report</button><br/><br/>
    </div>
</form>
<div class="modal fade modal1" id="certificate_status_modal" role="dialog">
    <div class="modal-dialog" style="margin-top:180px;">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header" style="background-color:#68dff0;border-top-left-radius:5px;border-top-right-radius:5px; ">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="color:#ffffff;">Training Status</h4>
            </div>
            <div class="modal-body" style="height:auto;">
                <form class="form-horizontal">
                    <fieldset>
                        <div id="certificate_status_show" class="form-group">
                            <span class="help-block"></span>
                            <div class="col-md-12" style="text-align: center">
                                <label for="inputtitle">No Data available</label>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>

    </div>
</div>
<div class="box box-info tbl-box">
    <div align="left" id="paglink"><?php echo $walkers->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
    <table class="table table-bordered">
        <tbody><tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Photo</th>
                <th>Bio</th>
                <th>Total Request</th>
                <th>Acceptance Rate</th>
                <th>Status</th>
                <th>Company</th>
                <th>Training Status</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($walkers as $walker) { ?>
                <tr>
                    <td><a href="/admin/driver/profile/<?= $walker->id ?>"><?= $walker->id ?></a></td>
                    <td><?php echo $walker->contact_name; ?> </td>
                    <td><?= $walker->email ?></td>
                    <td><?= $walker->phone ?></td>
                    <td>
                    <?php if($walker->picture=='' || $walker->picture==null) {
                        $driver_image = asset_url() . '/image/default_thumb.jpg';
                    ?>
                            <a href='#' target='_blank' onclick="window.open('<?php echo $driver_image ?>', 'popup', 'height=500px, width=400px'); return false;">View Photo</a>
                    <?php }else{ ?>
                            <a href='#' target='_blank' onclick="window.open('<?php echo $walker->picture; ?>', 'popup', 'height=500px, width=400px'); return false;">View Photo</a>
                    <?php } ?>
                    </td>
                    <td>
                        <?php
                        if ($walker->bio) {
                            echo $walker->bio;
                        } else {
                            echo "<span class='badge bg-red'>" . Config::get('app.blank_fiend_val') . "</span>";
                        }
                        ?>
                    </td>
                    <td><?= $walker->total_requests ?></td>
                    <td><?php
                        if ($walker->total_requests != 0) {
                            echo round(($walker->accepted_requests / $walker->total_requests) * 100, 2);
                        } else {
                            echo 0;
                        }
                        ?> %</td>
                    <td><?php
                        if ($walker->is_approved == 1) {
                            echo "<span class='badge bg-green'>Approved</span>";
                        } else {
                            echo "<span class='badge bg-red'>Pending</span>";
                        }
                        ?>
                    </td>
                    <td><?php
                        if ($walker->transportation_provider_id != null) {
                            echo $walker->transportation_provider_company ;
                        } else {
                            echo "<span>NA</span>";
                        }
                        ?>
                    </td>
                    <td>
                        <?php if($walker->certificate_status!=null && $walker->certificate_status!=''){ ?>
                                <a role="menuitem" id="certificate" tabindex="-1" href="/admin/driver/certificate_status/<?php echo $walker->id?>">View Status </a>
                        <?php } else { ?>
                                <a role="menuitem" id="certificate" tabindex="-1" href="javascript:void(0)"
                                   onclick="no_data()">View Status </a>
                        <?php } ?>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-flat btn-info dropdown-toggle" type="button" id="dropdownMenu1" name="action" data-toggle="dropdown">
                                Actions
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="{{ URL::Route('AdminProviderEdit', $walker->id) }}">Edit Details</a></li>
                                <li role="presentation"><a role="menuitem" tabindex="-2" href="javascript:void(0)" onclick="send_certificate('<?php echo $walker->id ?>');">Send Certificate</a></li>
                                <?php if ($walker->merchant_id == NULL && (Config::get('app.generic_keywords.Currency') == '$' || Config::get('app.default_payment') != 'stripe')) {
                                    ?>
                                    <li role="presentation"><a id="addbank" role="menuitem" tabindex="-1" href="{{ URL::Route('AdminProviderBanking', $walker->id) }}">Add Banking Details</a></li>
                                <?php } ?>
                                <li role="presentation"><a role="menuitem" id="history" tabindex="-1" href="{{ URL::Route('AdminProviderHistory', $walker->id) }}">View History</a></li>

                                <?php if ($walker->is_approved == 0) { ?>
                                    <li role="presentation"><a role="menuitem" id="approve" tabindex="-1" href="{{ URL::Route('AdminProviderApprove', $walker->id) }}">Approve</a></li>
                                <?php } else { ?>
                                    <li role="presentation"><a role="menuitem" id="decline" tabindex="-1" href="{{ URL::Route('AdminProviderDecline', $walker->id) }}">Decline</a></li>
                                    <li role="presentation"><a role="menuitem" id="decline" tabindex="-1" href="{{ URL::Route('AdminProviderDelete', $walker->id) }}">Delete</a></li>

                                <?php }
                                      if ($walker->is_authorize == 0) { ?>
                                        <li role="presentation"><a role="menuitem" id="authorize" tabindex="-1" href="{{ URL::Route('AdminProviderAuthorize', $walker->id) }}">Grant All Access</a></li>
                                <?php }else { ?>
                                        <li role="presentation"><a role="menuitem" id="noauthorize" tabindex="-1" href="{{ URL::Route('AdminProviderAccessDLC', $walker->id) }}">Access to DLC</a></li>
                                <?php } ?>
                                <?php
                                /* $settng = Settings::where('key', 'allow_calendar')->first();
                                  if ($settng->value == 1) { */
                                ?>
                                <!--<li role="presentation"><a role="menuitem" id="avail" tabindex="-1" href="{{ URL::Route('AdminProviderAvailability', $walker->id) }}">View Calendar</a></li>-->
                                <?php /* } */ ?>
                                <?php
                                $walker_doc = WalkerDocument::where('walker_id', $walker->id)->first();
                                if ($walker_doc != NULL) {
                                    ?>
                                    <li role="presentation"><a id="view_walker_doc" role="menuitem" tabindex="-1" href="{{ URL::Route('AdminViewProviderDoc', $walker->id) }}">View Documents</a></li>
                                <?php } else { ?>
                                    <li role="presentation"><a id="view_walker_doc" role="menuitem" tabindex="-1" href="#"><span class='badge bg-red'>No Documents</span></a></li>
                                <?php } ?>
                                <!--<li role="presentation"><a role="menuitem" id="history" tabindex="-1" href="{{ web_url().'/admin/provider/documents/'.$walker->id }}">View Documents</a></li>-->
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody></table>
    <div align="left" id="paglink"><?php echo $walkers->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
</div>
<script type="text/javascript">
    function no_data(){
        $('#certificate_status_modal').modal('show');
    }

    function send_certificate(walker_id){
        $.ajax({
            type: "POST",
            url:'<?php echo URL::Route('SendCertificate') ?>',
            data:{walker_id:walker_id},
            success: function(data) {
                console.log(data);
                if(data==1){
                    location.reload();
                }else{
                    alert('No any certificate send');
                }
            }
        });
    }
</script>


@stop