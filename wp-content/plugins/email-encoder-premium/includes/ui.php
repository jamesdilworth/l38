
<div class="wrap">

    <h1><?php _e( 'Email Address Encoder', 'email-address-encoder' ); ?></h1>

    <?php if ( ! count( array_filter( get_settings_errors() , function ( $error ) { return $error[ 'type' ] === 'error'; } ) ) ) : ?>

        <div class="card" style="float: left; margin-bottom: 0; margin-right: 1.25rem;">
            <h2 class="title">
                <?php _e( 'Signup for automatic warnings', 'email-address-encoder' ); ?>
            </h2>
            <p>
                <?php printf(
                    __( 'Receive an email notification when any page on <strong>%s</strong> contains unprotected email addresses.', 'email-address-encoder' ),
                    parse_url( get_home_url(), PHP_URL_HOST )
                ); ?>
            </p>
            <form method="post" action="<?php echo admin_url( 'options-general.php?page=email-address-encoder' ); ?>">
                <?php wp_nonce_field('subscribe'); ?>
                <input type="hidden" name="action" value="subscribe" />
                <p>
                    <input name="eae_notify_email" type="email" placeholder="<?php _e( 'Your email address...', 'email-address-encoder' ); ?>" class="regular-text" style="min-height: 28px;" required>
                    <?php submit_button( __( 'Notify me', 'email-address-encoder' ), 'primary', 'submit', false ); ?>
                </p>
            </form>
        </div>

        <div class="card" style="float: left; min-height: 146px; margin-bottom: 1.5rem;">
            <h2 class="title">
                <?php _e( 'Scan your pages', 'email-address-encoder' ); ?>
            </h2>
            <p>
                <?php _e( 'Don’t want automatic warnings? Use the page scanner to see whether all your email addresses are protected.', 'email-address-encoder' ); ?>
            </p>
            <p>
                <a class="button button-secondary" target="_blank" rel="noopener" href="https://encoder.till.im/scanner?utm_source=wp-plugin&amp;utm_medium=banner&amp;domain=<?php echo urlencode( get_home_url() ) ?>">
                    <?php _e( 'Open Page Scanner', 'email-address-encoder' ); ?>
                </a>
            </p>
        </div>

    <?php endif; ?>

    <form method="post" action="options.php">

        <?php settings_fields( 'email-address-encoder' ); ?>

        <table class="form-table">
            <tbody>

                <tr>
                    <th scope="row">
                        <?php _e( 'License key', 'email-address-encoder' ); ?>
                    </th>
                    <td>
                        <input name="eae_license_key" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'eae_license_key' ) ); ?>" style="text-security: disc; -webkit-text-security: disc; -moz-text-security: disc;">
                        <p class="description" style="max-width: 40em;">
                          <?php $license_key = get_option( 'eae_license_key' ); ?>
                          <?php if ( empty( $license_key ) ) : ?>

                              <?php printf(
                                  __( 'To unlock updates, please enter your license key. If you don’t have a license key, please see <a href="%s">details & pricing</a>.', 'email-address-encoder' ),
                                  'https://encoder.till.im/download?utm_source=wp-plugin&amp;utm_medium=unlock-msg'
                              ); ?>

                          <?php elseif ( $license = get_option( 'eae_license' ) ) : ?>

                              <?php $account_link = 'https://encoder.till.im/account?utm_source=wp-plugin&amp;utm_medium=license-msg'; ?>

                              <span <?php if ( isset( $license->licensee, $license->plan ) ) : ?>title="<?php echo esc_attr( sprintf( __( '[Licensee] %1$s [Plan] %2$s', 'email-address-encoder' ), $license->licensee, ucfirst( $license->plan ) ) ); ?>"<?php endif; ?>>

                                  <?php if ( $license->state === 'active' ) : ?>
                                      <span class="license-success">
                                          <?php _e( '✓ Valid license key', 'email-address-encoder' ); ?>
                                      </span>
                                  <?php elseif ( $license->state === 'past-due' ) : ?>
                                      <span class="license-warning">
                                          <?php printf( __( '✘ This license key will expire soon, because we are unable to charge your card. Please <a href="%s">update your billing information</a> to keep receiving updates.', 'email-address-encoder' ), $account_link ); ?>
                                      </span>
                                  <?php elseif ( $license->state === 'unpaid' ) : ?>
                                      <span class="license-danger">
                                          <?php printf( __( '✘ This license key has expired, because all attempts to charge your card have failed. Please <a href="%s">update your billing information</a> to re-enable updates.', 'email-address-encoder' ), $account_link ); ?>
                                      </span>
                                  <?php elseif ( $license->state === 'canceled' ) : ?>
                                      <span class="license-danger">
                                          <?php printf( __( '✘ This license key has expired, because the subscription was canceled. To re-enable updates, please <a href="%s">resume your subscription</a>.', 'email-address-encoder' ), $account_link ); ?>
                                      </span>
                                  <?php elseif ( $license->state === 'in-use' ) : ?>
                                      <span class="license-danger">
                                          <?php printf( __( '✘ This license key is already being used on another site. Please <a href="%s">upgrade your license</a> to enable updates.', 'email-address-encoder' ), $account_link ); ?>
                                      </span>
                                  <?php elseif ( $license->state === 'site-blocked' ) : ?>
                                      <span class="license-danger">
                                          <?php _e( '✘ This site has been blocked', 'email-address-encoder' ); ?>
                                      </span>
                                  <?php elseif ( $license->state === 'revoked' ) : ?>
                                      <span class="license-danger">
                                          <?php _e( '✘ This license key has been revoked', 'email-address-encoder' ); ?>
                                      </span>
                                  <?php else : ?>
                                      <span class="license-danger">
                                          <?php _e( '✘ Invalid license key', 'email-address-encoder' ); ?>
                                      </span>
                                  <?php endif; ?>

                              </span>

                          <?php endif; ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php _e( 'Search for emails using', 'email-address-encoder' ); ?>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span><?php _e( 'Search for emails using', 'email-address-encoder' ); ?></span>
                            </legend>
                            <label>
                                <input type="radio" name="eae_search_in" value="filters" <?php checked( 'filters', get_option( 'eae_search_in' ) ); ?>>
                                <?php _e( 'WordPress filters', 'email-address-encoder' ); ?>
                                <p class="description">
                                    <small><?php _e( 'Protects email addresses in filtered sections only.', 'email-address-encoder' ); ?></small>
                                </p>
                            </label>
                            <br>
                            <label>
                                <input type="radio" name="eae_search_in" value="output" <?php checked( 'output', get_option( 'eae_search_in' ) ); ?> <?php disabled( true, eae_license_was_revoked() ); ?>>
                                <?php _e( 'Full-page scanner', 'email-address-encoder' ); ?>
                                <p class="description">
                                    <small><?php _e( 'Protects all email addresses on your site.', 'email-address-encoder' ); ?></small>
                                </p>
                            </label>
                            <br>
                            <label>
                                <input type="radio" name="eae_search_in" value="void" <?php checked( 'void', get_option( 'eae_search_in' ) ); ?>>
                                <?php _e( 'Nothing, don’t do anything', 'email-address-encoder' ); ?>
                                <p class="description">
                                    <small><?php _e( 'Turns off email protection.', 'email-address-encoder' ); ?></small>
                                </p>
                            </label>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php _e( 'Protect emails using', 'email-address-encoder' ); ?>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span><?php _e( 'Protect emails using', 'email-address-encoder' ); ?></span>
                            </legend>

                            <label>
                                <input type="radio" name="eae_technique" value="entities" <?php checked( 'entities', get_option( 'eae_technique' ) ); ?>>
                                <?php _e( 'HTML entities', 'email-address-encoder' ); ?>
                                <p class="description">
                                    <small><?php _e( 'Offers good protection and works in most scenarios.', 'email-address-encoder' ); ?></small>
                                </p>
                            </label>
                            <br>

                            <label>
                                <input type="radio" name="eae_technique" value="css-direction" <?php checked( 'css-direction', get_option( 'eae_technique' ) ); ?> <?php disabled( true, eae_license_was_revoked() ); ?>>
                                <?php _e( 'CSS direction', 'email-address-encoder' ); ?>
                                <p class="description">
                                    <small><?php _e( 'Protects against smart robots without the need for JavaScript.', 'email-address-encoder' ); ?></small>
                                </p>
                            </label>
                            <br>

                            <label>
                                <input type="radio" name="eae_technique" value="rot13" <?php checked( 'rot13', get_option( 'eae_technique' ) ); ?> <?php disabled( true, eae_license_was_revoked() ); ?>>
                                <?php _e( 'ROT13 encoding', 'email-address-encoder' ); ?>
                                <p class="description">
                                    <small><?php _e( 'Offers the best protection, but requires JavaScript.', 'email-address-encoder' ); ?></small>
                                </p>
                            </label>
                            <br>

                            <label>
                                <input type="radio" name="eae_technique" value="rot47" <?php checked( 'rot47', get_option( 'eae_technique' ) ); ?> <?php disabled( true, eae_license_was_revoked() ); ?>>
                                <?php _e( 'Polymorphous ROT47/CSS', 'email-address-encoder' ); ?>
                                <p class="description">
                                    <small><?php _e( 'State-of-the-art protection against smart robots, but requires JavaScript.', 'email-address-encoder' ); ?></small>
                                </p>
                            </label>

                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php _e( 'Filter priority', 'email-address-encoder' ); ?>
                    </th>
                    <td>
                        <input name="eae_filter_priority" type="number" min="1" value="<?php echo esc_attr( EAE_FILTER_PRIORITY ); ?>" class="small-text">
                        <p class="description" style="max-width: 40em;">
                            <?php _e( 'The filter priority specifies when the plugin searches for and encodes email addresses. The default value of <code>1000</code> ensures that all other plugins have finished their execution and no emails are missed.', 'email-address-encoder' ); ?>
                        </p>
                    </td>
                </tr>

            </tbody>
        </table>

        <p class="submit">
            <?php submit_button( null, 'primary large', 'submit', false ); ?>
        </p>

    </form>

</div>
