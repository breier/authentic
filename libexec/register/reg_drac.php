<?php if(isset($_POST['ajax'])) { ?>
								<h4 class="text-center" style="margin-top: 0;"><?= $_msg->lang("Unknown Equipment") .": $maca"; ?></h4>
								<h4 class="text-center" style="margin-bottom: 20px;"><?= $_msg->lang("Provide authentication information"); ?></h4>
								<div id="pnsc"></div>
								<label class="control-label col-md-2 col-sm-2 col-ms-2 col-xs-3" for="srvc">
									<?= $_msg->lang("Service"); ?>
								</label>
								<div class="col-md-4 col-sm-4 col-ms-4 col-xs-9" style="margin-bottom: 16px;">
									<select name="srvc" id="srvc" class="form-control selectpicker">
										<option value="ssh">ssh</option>
									</select>
								</div>
								<label class="control-label col-md-2 col-sm-2 col-ms-2 col-xs-3" for="port">
									<?= $_msg->lang("Port"); ?>
								</label>
								<div class="col-md-4 col-sm-4 col-ms-4 col-xs-9">
									<input type="text" name="port" id="port" value="22" data-error="<?= $_msg->lang('Invalid').' '.$_msg->lang('Port').'!'; ?>" class="form-control col-md-7 col-xs-12"/>
								</div>
								<label class="control-label col-md-2 col-sm-2 col-ms-2 col-xs-3" for="usrnm">
									<?= $_msg->lang("Username"); ?>
								</label>
								<div class="col-md-4 col-sm-4 col-ms-10 col-xs-9">
									<input type="text" name="usrnm" id="usrnm" data-error="<?= $_msg->lang('Invalid').' '.$_msg->lang('Username').'!'; ?>" class="form-control col-md-7 col-xs-12" >
								</div>
								<label class="control-label col-md-2 col-sm-2 col-ms-2 col-xs-3" for="pass">
									<?= $_msg->lang("Password"); ?>
								</label>
								<div class="col-md-4 col-sm-4 col-ms-10 col-xs-9">
									<div class="input-group">
										<input type="password" name="pass" id="pass" data-error="<?= $_msg->lang('Invalid').' '.$_msg->lang('Password').'!'; ?>" aria-describedby="btnad" class="form-control col-md-7 col-xs-12"/>
										<span id="btnad" class="input-group-addon" style="cursor: pointer;" title="<?= $_msg->lang('show / hide'); ?>"><i class="fa fa-eye"></i></span>
									</div>
								</div>
								<div class="col-md-12">
									<button type="button" onclick="form_seai();" class="btn btn-primary pull-right regsend"><?= $_msg->lang("Send"); ?></button>
								</div>
								<script src="<?= $_path->js; ?>/bootstrap-select.min.js"></script>
								<script type="text/javascript">
									$("#btnad").on("click", function() {
										$('#pass').focus();
										$(this).find('i').toggleClass('fa-eye-slash').toggleClass('fa-eye');
										$('#pass').attr('type', ($('#pass').attr('type')=='text')?('password'):('text'));
									});
									$(".selectpicker").selectpicker();
								</script>
<?php } ?>
