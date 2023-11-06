<?php
class AdminMenusForZoho {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'register' ), -1 );
	}

	public function register() {
			add_submenu_page(
				'edit.php?post_type=w3s_cf7',
				'Zoho Auth Settings',
				'Zoho Auth Settings',
				'manage_options',
				'w3s-cf7-zoho',
				array( $this, 'createAdminPage' )
			);
	}

	public function createAdminPage() {

		$redirectURLEncoded = urlencode_deep( admin_url( 'edit.php?post_type=w3s_cf7&page=w3s-cf7-zoho' ) );

		$redirectURL = admin_url( 'edit.php?post_type=w3s_cf7&page=w3s-cf7-zoho' );
		$siteURL     = parse_url( site_url() )['host'];

		$dataSet = new ZohoAuthInfos();
		$dataSet->storeInfo( $_POST );
		$dataSet->refreshToken( $_GET );

		$zcid       = $dataSet->getInfo( 'zoho_client_id' );
		$dataCenter = $dataSet->getInfo( 'data_center' );

		?>
<div class="">
    <?php do_action( '_message_' ); ?>
    <h2>Zoho Auth Settings</h2>
    <hr />
    <form method="post">
        <table class="zoho-auth-info">
            <tr>
                <td colspan="2">
                    <h3>Information to create Zoho Client :: </h3>
                </td>
            </tr>
            <tr>
                <td>
                    <h4>No Zoho CRM Account?</h4>
                </td>
                <td>
                    <a target="_blank"
                        href="https://payments.zoho.com/ResellerCustomerSignUp.do?id=4c1e927246825d26d1b5d89b9b8472de"><b>Create
                            FREE Account!</b></a>
                </td>
            </tr>
            <tr>
                <td>
                    <h4>Client Name:</h4>
                </td>
                <td><code>W3S CF7 to CRM</code></td>
            </tr>
            <tr>
                <td>
                    <h4>Client Domain:</h4>
                </td>
                <td><code><?php echo $siteURL; ?></code></td>
            </tr>
            <tr>
                <td>
                    <h4>Authorized redirect URIs:</h4>
                </td>
                <td><code><?php echo $redirectURL; ?></code></td>
            </tr>
            <tr>
                <td>
                    <h4>Client Type</h4>
                </td>
                <td><code>Web Based</code></td>
            </tr>
            <tr>
                <td colspan="2">
                    <h3>Zoho Credentials :: </h3>
                </td>
            </tr>
            <tr>
                <td>
                    <h4>Data Center:</h4>
                </td>
                <td>
                    <?php
				foreach ( array(
					'zoho.com'    => '.com',
					'zoho.eu'     => '.eu',
					'zoho.com.au' => '.com.au',
					'zoho.in'     => '.in',
				) as $k => $v ) {
					$selected = ( $dataCenter == $v ) ? "checked='checked'" : '';

					echo "<label style='margin-right:20px'><input type='radio' name='data_center' value='$v' $selected><span>$k</span></label>";
				}
				?>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <h4 class="zci">Zoho Client ID</h4>
                </td>
                <td>
                    <input type="text" name="zoho_client_id" id="zoho_client_id"
                        value="<?php echo $dataSet->getInfo( 'zoho_client_id' ); ?>">
                    <p class="guid">
                        Your Zoho App Client ID. To Generate, Please follow <a
                            href="https://www.zoho.com/crm/help/developer/api/register-client.html" target="_blank">this
                            instructions.</a>
                    </p>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <h4 class="zcs">Zoho Client Secret</h4>
                </td>
                <td>
                    <input type="password" name="zoho_client_secret" id="zoho_client_secret"
                        value="<?php echo $dataSet->getInfo( 'zoho_client_secret' ); ?>">
                    <p class="guid">
                        Your Zoho App Client Secret. To Generate, Please follow <a
                            href="https://www.zoho.com/crm/help/developer/api/register-client.html" target="_blank">this
                            instructions.</a>
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <h4>Zoho User EmailD</h4>
                </td>
                <td>
                    <input type="text" name="zoho_user_email" id="zoho_user_email"
                        value="<?php echo $dataSet->getInfo( 'zoho_user_email' ); ?>">
                </td>
            </tr>
            <?php
		if ( $dataSet->getInfo( 'zoho_client_id' ) && $dataSet->getInfo( 'data_center' ) ) :
			?>
            <tr>
                <td>
                    <h4>Authorize Zoho Account</h4>
                </td>
                <td>
                    <?php
				echo "<a href='https://accounts.zoho$dataCenter/oauth/v2/auth?scope=ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,aaaserver.profile.READ&client_id=$zcid&response_type=code&access_type=offline&prompt=consent&redirect_uri=$redirectURLEncoded'><b>Grant Access</b></a>";
			?>
                </td>
                <?php endif ?>
            <tr>
                <td colspan="2">
                    <div style="margin-top: 20px">
                        <button name="store_zoho_info" value="save" class="button button-primary">Save & Bring Grant
                            Access</button>
                    </div>
                </td>
            </tr>

        </table>
    </form>
</div>
<?php
	}
}