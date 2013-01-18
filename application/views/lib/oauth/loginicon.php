<div class="hezuo_login"> <strong>使用其他帐号登录</strong>
	<?php foreach ($sitesConfig as $site => $config){?>
		<span><i class="hezuo_<?php echo $site;?>"></i>
			<a class="<?php echo $site;?>link" title="<?php echo $site;?>" target='_blank' href="<?php echo site_url('oauth/login/'.$site)?>" ><?php echo lang('oauth_'.$site);?></a>
		</span>
		<b>|</b>
	<?php }?>
</div>