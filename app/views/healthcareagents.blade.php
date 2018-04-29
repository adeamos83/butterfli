@extends('layout')

@section('content')
<div class="box box-info tbl-box">
    <div align="left" id="paglink"><?php echo $healthagents->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
    <table class="table table-bordered">
        <tbody><tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Company</th>
                <th>CompanyLogo</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($healthagents as $healthagent) { ?>
                <tr>
                    <td><?= $healthagent->id ?></td>
                    <td><?php echo $healthagent->contact_name; ?> </td>
                    <td><?= $healthagent->email ?></td>
                    <td><?= $healthagent->company ?></td>
                    <td style="width:10%"><p class="centered"><a href="#">
                                <img src="<?= $healthagent->companylogo ?>" class="img-circle" width="50%" alt="Logo" /></a></p></td>
                    <td>
                    <?php
                        if ($healthagent->is_active == 1) {
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
                                <?php if ($healthagent->is_active == 0) { ?>
                                    <li role="presentation"><a role="menuitem" id="approve" tabindex="-1" href="{{ URL::Route('AdminHealthcareAgentsApprove', $healthagent->id) }}">Approve</a></li>
                                <?php } else { ?>
                                    <li role="presentation"><a role="menuitem" id="decline" tabindex="-1" href="{{ URL::Route('AdminHealthcareAgentsDecline', $healthagent->id) }}">Decline</a></li>

                                <?php } ?>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody></table>
    <div align="left" id="paglink"><?php echo $healthagents->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
</div>



@stop