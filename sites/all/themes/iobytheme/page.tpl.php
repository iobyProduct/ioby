<?php
// $Id: page.tpl.php,v 1.1.2.5 2010/08/13 20:29:13 spaceninja Exp $

/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/garland.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['highlighted']: Items for the highlighted content region.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['header']: Items for the header region.
 * - $page['footer']: Items for the footer region.
 *
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see template_process()
 */

?>

<div class="page-wrapper">
  <!--[if lte IE 8]>
  <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
  <![endif]-->
  <a id="bypass-link-main" class="screen-reader-text" href="#main">Skip Navigation</a>

  <div class="page-wrapper-inside">
    <header class="page-header refresh-header">
      <div class="page-header__inside">
        <div class="page-header__left">
          <div class="page-header__logo">
            <a href="/">
              <h1 class="visually-hidden">ioby</h1>
              <img src="/sites/all/themes/iobytheme/img/ioby-logo.svg" alt="ioby" />
            </a>
          </div>
          <nav class="page-header__project-nav">
            <ul>
              <li><?php print l('Find a Project', 'projects/browse', [
                  'query' => array('f' => array('sm_field_project_status:1')),
                ]);
                ?></a></li>
              <li><?php print l('Create a Project', 'idea'); ?></li>
            </ul>
          </nav>
        </div><!--/.page-header__left-->

        <div class="page-header__right">
          <nav class="page-header__primary-nav">
            <?php print $main_menu; ?>
          </nav>
          <div class="page-header__search">
            <div class="page-header__search-trigger">
              <button><span class="visually-hidden">Show Search</span><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><path d="M19.542 16.91l-4.774-4.772a7.772 7.772 0 0 0 .84-1.928c.198-.692.297-1.409.297-2.15a7.48 7.48 0 0 0-.655-3.079 8.365 8.365 0 0 0-1.769-2.57A8.687 8.687 0 0 0 10.91.654 7.465 7.465 0 0 0 7.817 0a7.608 7.608 0 0 0-3.043.618 8.046 8.046 0 0 0-2.498 1.669A7.622 7.622 0 0 0 .606 4.77 7.73 7.73 0 0 0 0 7.812C0 8.9.214 9.934.643 10.915a8.19 8.19 0 0 0 1.756 2.57 8.687 8.687 0 0 0 2.573 1.756 7.465 7.465 0 0 0 3.092.655c.726 0 1.418-.095 2.078-.284.66-.19 1.286-.45 1.88-.779l4.799 4.796c.23.247.515.371.853.371.338 0 .623-.124.853-.37l1.188-1.187c.23-.231.321-.487.272-.767-.05-.28-.198-.535-.445-.766zM2.399 7.812c0-.742.14-1.442.42-2.101.281-.66.669-1.232 1.164-1.718A5.707 5.707 0 0 1 5.714 2.83a5.19 5.19 0 0 1 2.103-.433c.758 0 1.48.157 2.164.47.684.313 1.286.729 1.806 1.248.52.52.932 1.12 1.237 1.805.305.684.457 1.397.457 2.138 0 .758-.14 1.467-.42 2.126a5.277 5.277 0 0 1-1.163 1.719 5.707 5.707 0 0 1-1.731 1.162 5.19 5.19 0 0 1-2.103.432 5.086 5.086 0 0 1-2.14-.47A6.152 6.152 0 0 1 4.12 11.78a6.148 6.148 0 0 1-1.25-1.805 5.134 5.134 0 0 1-.47-2.163z" fill="#000" fill-rule="evenodd"/></svg></button>
            </div>
            <div class="page-header__search-content">
              <?php print render($page['search']); ?>
            </div><!--/.header__search-content-->

          </div><!--/.header__search-->
          <?php if (!empty($cart_items) && $cart_items > 0): ?>
          <div class="page-header__cart">
            <a href="/cart">
              <span class="page-header__cart-icon"><svg width="28" height="26" xmlns="http://www.w3.org/2000/svg"><path d="M1.93 1c-.514 0-.93.403-.93.9s.416.9.93.9h3.091c1.54 3.69 3.057 7.385 4.584 11.081L8.2 17.153a.891.891 0 0 0 .085.847c.17.246.472.401.777.4h15.504c.492.007.944-.424.944-.9 0-.476-.452-.907-.944-.9H10.458l.804-1.856L23.393 14a.948.948 0 0 0 .833-.693l2.755-8.211c.122-.525-.355-1.099-.911-1.097H7.521L6.514 1.562A.954.954 0 0 0 5.65 1h-3.72zm6.338 4.8h16.63l-2.338 6.467-11.337.677L8.268 5.8zM12.163 19c-1.702 0-3.1 1.354-3.1 3s1.398 3 3.1 3c1.702 0 3.1-1.354 3.1-3s-1.398-3-3.1-3zm9.303 0c-1.702 0-3.101 1.354-3.101 3s1.399 3 3.1 3c1.702 0 3.101-1.354 3.101-3s-1.399-3-3.1-3zm-9.303 1.8c.696 0 1.24.527 1.24 1.2 0 .673-.544 1.2-1.24 1.2s-1.24-.527-1.24-1.2c0-.673.544-1.2 1.24-1.2zm9.303 0c.695 0 1.24.527 1.24 1.2 0 .673-.545 1.2-1.24 1.2-.697 0-1.24-.527-1.24-1.2 0-.673.543-1.2 1.24-1.2z" fill="#000" fill-rule="nonzero" stroke="#000" stroke-width=".3"/></svg></span>
              <span class="page-header__cart-number"><?php print $cart_items; ?></span>
            </a>
          </div><!--/.header__cart-->
          <?php endif; ?>
          <nav class="page-header__auth-nav">
            <ul>
              <li>
                <?php if ($logged_in):
                  print l('My Account','user');
                else:
                  print l('Sign Up','user/register' );
                endif;
                ?>
              </li>
              <?php if ($logged_in):?>
              <li>
                <?php print l('Log Out','user/logout'); ?>
              </li>
              <?php else: ?>
                <li class="page-header__login">
                  <button class="page-header__login-trigger">Log In</button>
                  <div class="page-header__login-content">
                    <?php print render($page['loginpop']); ?>
                  </div><!--/.login-form-->
                </li>
              <?php endif; ?>

              <?php if (!empty($user_email) || !empty($user_first_name) || !empty($user_last_name)): ?>
                <span id="user_data" style="display:none;"
                  <?php if (!empty($user_email)) { ?>
                    data-email="<?php print $user_email ?>" <?php }
                  if (!empty($user_first_name)) { ?>
                    data-first-name="<?php print $user_first_name; ?>" <?php }
                  if (!empty($user_last_name)) { ?>
                    data-last-name="<?php print $user_last_name; ?>" <?php } ?>
                ></span>
              <?php endif; ?>
            </ul>
          </nav>
          <div class="page-header__offcanvas-trigger">
            <button>
              <span class="page-header__offcanvas-trigger-text">Menu</span>
              <span class="page-header__offcanvas-trigger-icon"></span>
            </button>
          </div>
        </div><!--/.page-header__right-->
      </div><!--/.page-header__inside-->
    </header>
    <div class="offcanvas displaynone">
      <div class="offcanvas__content">
        <div class="offcanvas__top">
          <button class="offcanvas__close">
            <span class="offcanvas__close-text">Close</span>
            <span class="offcanvas__close-icon"></span>
          </button>
          <nav class="offcanvas__primary-nav">
            <?php print $main_menu; ?>
          </nav>
          <div class="offcanvas__search">
            <?php print render($page['search']); ?>
          </div>
        </div><!--/.offcanvas__top-->
        <div class="offcanvas__bottom">
          <nav class="offcanvas__auth-nav">
            <ul>
              <li>
                <?php if ($logged_in):
                  print l('My Account','user');
                else:
                  print l('Sign Up','user/register' );
                endif;
                ?>
              </li>
              <li class="offcanvas__login">
                <?php if ($logged_in):
                  print l('Log Out','user/logout');
                else:
                  print l('Log In','user', array('attributes'=>array('class'=>array('login'))));
                  print render($page['loginpop']);
                endif;
                ?>
              </li>
            </ul>
          </nav>
        </div><!--/.offcanvas__bottom-->
      </div><!--/.offcanvas__content-->
    </div><!--/.offcanvas-->

  <div id="page">

  <!--<header id="siteheader" role="banner">

    <?php /*if ($logo): */?>
        <a href="<?php /*print $front_page; */?>" title="<?php /*print t('Home'); */?>" rel="home" id="logo"><?php /*print $site_name; */?></a>


    <?php /*endif; */?>

    <?php /*if ($site_slogan): */?>
      <div id="intro">
        <p>
          <?php /*print $site_slogan; */?>
        </p>
        <div id="greeting">
          <?php
/*          print l('start a project', 'idea', array('attributes' => array('class' => array('button'))));
          print l('find a project »', 'projects/browse', array(
            'attributes' => array(
              'class' => array(
                'button',
                'button-blue'
              )
            ),
            'query' => array('f' => array('sm_field_project_status:1')),
          ));
          */?>
        </div>
      </div>
    <?php /*endif; */?>
  </header>-->

  <?php if (!empty($bundle) && $bundle == 'homepage') {} else { ?>
 <header id="pageheader">
    <div class="full">
      <?php if ($breadcrumb && $is_admin): ?><!--
        <div id="breadcrumb"><?php print $breadcrumb; ?></div>
      --><?php endif; ?>


        <?php print render($title_prefix); ?>
        <h1 id="pagetitle"><?php print $title; ?></h1>
        <?php print render($title_suffix); ?>

      <?php print render($page['header']); ?>

      <?php if (isset($node) && $node->type == 'project_2') :
      // Don't show on edit form
      if(arg(0) == 'node' && arg(2) !== 'edit'){
        if (isset($node->field_project_inbrief['und'][0]['safe_value'])) {
          print '<p>'.text_summary($node->field_project_inbrief['und'][0]['safe_value'],null,230).'</p>';
        }
        elseif (isset($node->body['und'][0]['safe_value'])) {
          print '<p>'.text_summary($node->body['und'][0]['safe_value'],null,230).'</p>';
        }
      }
      endif; ?>
<!--      --><?php //if(drupal_is_front_page()):
//      ?>
<!--      <div class="mobileheader">-->
<!--        <div class="full">-->
<!--          <img src="/files/ioby%20EOY%20carousel%20banner-01%202.png">-->
<!--          <img src="/files/LFAL_slide.jpg">-->
<!--          <img src="/files/big_idea2.png">-->
<!--          <img src="/files/pfp%20match%20tile.png">-->
<!--        </div>-->
<!--      </div>-->
<!--      --><?php //endif;
    ?>
    </div>
  </header>
  <?php } ?>

  <main id="main-wrapper">

  <?php $pageClasses ="";
    if($page['sidebar']) $pageClasses .= " with-sidebar";
    if($page['search_facets']) $pageClasses .= " with-sidebar-left";

  ?>

  <div id="main" class="clearfix<?php print $pageClasses ?>">
    <?php print render($page['subhead']); ?>

    <?php if ($page['search_facets']): ?>
      <div id="current-search">
        <?php print render($page['current_search']) ?>
      </div>
    <?php endif; ?>

    <div id="content" class="column" role="main">
      <?php if ($page['highlighted']): ?>
        <div id="highlighted"><?php print render($page['highlighted']); ?></div>
      <?php endif; ?>

      <?php print $messages; ?>

      <?php if ($tabs): ?>
        <div class="tabs"><?php print render($tabs); ?></div>
      <?php endif; ?>
      <?php print render($page['help']); ?>
      <?php if ($action_links): ?>
        <ul class="action-links"><?php print render($action_links); ?></ul>
      <?php endif; ?>
      <?php print render($page['content']); ?>
      <?php print $feed_icons; ?>
    </div>
    <!-- /.section, /#content -->

    <?php if ($page['search_facets']): ?>
      <div id="sidr-facets">
        <?php print render($page['search_facets']); ?>
      </div>
    <?php endif; ?>


    <?php if ($page['sidebar']): ?>
    <div id="rail" class="column">
      <?php print render($page['sidebar']); ?>
    </div>
    <?php endif; ?>

  </div><!-- /#main -->

  </main><!--/#main-wrapper-->

    <footer class="page-footer">
      <div class="page-footer__top-area">
        <div class="page-footer__top-area-inside">
          <figure class="page-footer__top">
            <?php print render($testimonial); ?>
            <?php /* <blockquote class="page-footer__quote">
              <p>I used to dread fundraising. It's great knowing that all the supports and personal attention will be there to make the process painless and effective. We've had a great, memorable and transformative experience.”</p>
            </blockquote>
            <figcaption class="page-footer__caption">
              <span><strong>Joe Matunis</strong></span>
              <span>El Puente Cycling Club, Project Leader,</span>
              <span>Brooklyn, NY</span>
            </figcaption>*/ ?>
          </figure>

          <div class="page-footer__featured">
            <div class="page-footer__featured-label" >Featured In</div>
            <ul class="page-footer__featured-list">
              <li><img alt="Atlantic Cities" src="/sites/all/themes/iobytheme/img/ioby_featuredInLogo_atlanticCities.png"/></li>
              <li><img alt="Next American City" src="/sites/all/themes/iobytheme/img/ioby_featuredInLogo_nextAmericanCity.png"/></li>
              <li><img alt="Time" src="/sites/all/themes/iobytheme/img/ioby_featuredInLogo_time.png"/></li>
              <li><img alt="WNYC" src="/sites/all/themes/iobytheme/img/ioby_featuredInLogo_WNYC.png"/></li>
              <li><img alt="Fast Company" src="/sites/all/themes/iobytheme/img/ioby_featuredInLogo_fastCo.png"/></li>
              <li><img alt="The Wall Street Journal" src="/sites/all/themes/iobytheme/img/ioby_featuredInLogo_wallStreet.png"/></li>
            </ul>
          </div><!--/.page-footer__featured-->
        </div><!--/.page-footer__top-area-inside-->
      </div><!--/.page-footer__top-area-->
      <div class="page-footer__bottom-area">
        <div class="page-footer__bottom-area-inside">

          <div class="page-footer__bottom">

            <?php print render($page['footer']); ?>

            <div class="page-footer__left">
              <?php print $footer_menu; ?>
            </div><!--/.page-footer__left-->

            <div class="page-footer__right">

              <div class="page-footer__get-in-touch">
                <div class="page-footer__get-in-touch-label">Get in Touch:</div>
                <div class="page-footer__get-in-touch-body">917.464.4515 or <a href="http://support.ioby.org/customer/portal/emails/new">Email</a></div>
              </div><!--/.page-footer__get-in-touch-->

              <div class="page-footer__signup">
                <div class="page-footer__signup-label">Join Our Newsletter</div>
                <form name="footer-signup" action="https://page.ioby.org/Subscribe-Website.html" method="GET">
                  <label class="visually-hidden" for="footer-email">Email</label>
                  <div class="page-footer__signup-form">
                    <input id="footer-email" type="email" name="email" placeholder="Email address" required/>
                    <input type="submit" value="Sign Up"/>
                  </div>
                </form>
              </div><!--/.page-footer__signup-->

              <div class="page-footer__follow">
                <div class="page-footer__follow-label">Follow <span>Ioby</span></div>
                <nav class="page-footer__follow-social-nav">
                  <ul>
                    <li><a href="http://twitter.com/ioby">
                        <svg width="32px" height="26px" viewBox="0 0 32 26" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                          <!-- Generator: Sketch 48.2 (47327) - http://www.bohemiancoding.com/sketch -->
                          <desc>Created with Sketch.</desc>
                          <defs></defs>
                          <g id="R3" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g id="ROUND-3—no-cart,-one-promo-Copy" transform="translate(-971.000000, -7382.000000)" fill="#3DA9E0">
                              <g id="Footer" transform="translate(0.000000, 6840.000000)">
                                <g id="Group-6">
                                  <g id="Group-3" transform="translate(179.000000, 0.000000)">
                                    <g id="Group" transform="translate(670.000000, 539.000000)">
                                      <path d="M154,6.04000241 C153.093061,7.34667498 151.996626,8.45999525 150.710661,9.37999904 C150.724197,9.56666719 150.730964,9.84666718 150.730964,10.219999 C150.730964,11.9533423 150.473776,13.6833255 149.95939,15.4100026 C149.445009,17.1366751 148.663287,18.7933253 147.614212,20.3800023 C146.565142,21.9666749 145.316422,23.3699945 143.868022,24.5900001 C142.419623,25.8100062 140.673446,26.7833296 138.629442,27.5099999 C136.585439,28.2366703 134.399335,28.6 132.071068,28.6 C128.402689,28.6 125.045701,27.6333428 122,25.7000003 C122.473776,25.7533338 123.001689,25.7800001 123.583756,25.7800001 C126.629455,25.7800001 129.343475,24.8600093 131.725889,23.019999 C130.30456,22.993334 129.032154,22.5633392 127.908629,21.730001 C126.785104,20.8966628 126.013538,19.8333393 125.593909,18.5399992 C126.040611,18.6066661 126.453467,18.6400018 126.832488,18.6400018 C127.414556,18.6400018 127.989847,18.5666687 128.558375,18.4200024 C127.042293,18.113333 125.786807,17.370007 124.791878,16.1900019 C123.796949,15.0099968 123.299493,13.6400084 123.299493,12.0800008 L123.299493,12.0000015 C124.219971,12.5066715 125.208117,12.7800008 126.26396,12.8200027 C125.370554,12.2333333 124.659901,11.4666709 124.13198,10.5200022 C123.604058,9.57332895 123.340102,8.54667428 123.340102,7.44000234 C123.340102,6.26666352 123.637899,5.18000821 124.233502,4.18000058 C125.871413,6.16667888 127.864625,7.75666216 130.2132,8.9499997 C132.561771,10.1433417 135.076129,10.8066684 137.756347,10.9400022 C137.648054,10.4333321 137.593907,9.94000349 137.593907,9.46000288 C137.593907,7.67332521 138.233497,6.1500088 139.512689,4.88999991 C140.791886,3.62999549 142.3384,3 144.152286,3 C146.047386,3 147.644663,3.67999229 148.944163,5.03999926 C150.419638,4.75999927 151.807101,4.2400057 153.106602,3.48000062 C152.605751,5.01334326 151.64468,6.19999664 150.223351,7.04000108 C151.48224,6.90666732 152.741111,6.57333742 154,6.04000241 Z" id="Page-1"></path>
                                    </g>
                                  </g>
                                </g>
                              </g>
                            </g>
                          </g>
                        </svg></a></li>
                    <li><a href="http://facebook.com/ioby.org">
                        <svg width="30px" height="30px" viewBox="0 0 30 30" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                          <!-- Generator: Sketch 48.2 (47327) - http://www.bohemiancoding.com/sketch -->
                          <desc>Created with Sketch.</desc>
                          <defs></defs>
                          <g id="R3" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g id="ROUND-3—no-cart,-one-promo-Copy" transform="translate(-1024.000000, -7380.000000)" fill="#3DA9E0">
                              <g id="Footer" transform="translate(0.000000, 6840.000000)">
                                <g id="Group-6">
                                  <g id="Group-3" transform="translate(179.000000, 0.000000)">
                                    <g id="Group" transform="translate(670.000000, 539.000000)">
                                      <path d="M203.339845,1 C203.795576,1 204.186198,1.16275875 204.511719,1.48828061 C204.837241,1.81380248 205,2.20442435 205,2.66015497 L205,29.3398437 C205,29.7955752 204.837241,30.1861962 204.511719,30.5117189 C204.186198,30.8372412 203.795576,31 203.339845,31 L195.703125,31 L195.703125,19.3789065 L199.589844,19.3789065 L200.175779,14.8476578 L195.703125,14.8476578 L195.703125,11.9570298 C195.703125,11.2278617 195.856119,10.680991 196.162111,10.3164048 C196.468098,9.95182294 197.063798,9.76952982 197.94922,9.76952982 L200.332033,9.74999982 L200.332033,5.70703178 C199.511716,5.58984303 198.35287,5.53124866 196.85547,5.53124866 C195.084627,5.53124866 193.668623,6.05207927 192.607423,7.09374925 C191.546219,8.1354236 191.015623,9.60676232 191.015623,11.5078135 L191.015623,14.8476578 L187.109374,14.8476578 L187.109374,19.3789065 L191.015623,19.3789065 L191.015623,31 L176.660156,31 C176.204425,31 175.813804,30.8372412 175.488281,30.5117189 C175.162759,30.1861962 175,29.7955752 175,29.3398437 L175,2.66015497 C175,2.20442435 175.162759,1.81380248 175.488281,1.48828061 C175.813804,1.16275875 176.204425,1 176.660156,1 L203.339845,1 Z" id="Page-1"></path>
                                    </g>
                                  </g>
                                </g>
                              </g>
                            </g>
                          </g>
                        </svg></a></li>
                    <li><a href="http://instagram.com/inourbackyards/">
                        <svg width="32px" height="32px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                          <!-- Generator: Sketch 48.2 (47327) - http://www.bohemiancoding.com/sketch -->
                          <desc>Created with Sketch.</desc>
                          <defs></defs>
                          <g id="R3" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g id="ROUND-3—no-cart,-one-promo-Copy" transform="translate(-1129.000000, -7379.000000)" fill="#3DA9E0" fill-rule="nonzero">
                              <g id="Footer" transform="translate(0.000000, 6840.000000)">
                                <g id="Group-6">
                                  <g id="Group-3" transform="translate(179.000000, 0.000000)">
                                    <g id="Group" transform="translate(670.000000, 539.000000)">
                                      <path d="M290.667991,15.9999448 C290.667991,13.0545307 293.055183,10.6662437 296.000662,10.6662437 C298.946142,10.6662437 301.334602,13.0545307 301.334602,15.9999448 C301.334602,18.945359 298.946197,21.333646 296.000662,21.333646 C293.055127,21.333646 290.667991,18.945359 290.667991,15.9999448 M287.78458,15.9999448 C287.78458,20.5375135 291.462912,24.2157227 296.000717,24.2157227 C300.538577,24.2157227 304.216854,20.5375135 304.216854,15.9999448 C304.216854,11.4623761 300.538522,7.78416701 296.000717,7.78416701 C291.462857,7.78416701 287.78458,11.4623761 287.78458,15.9999448 M302.621881,7.45848649 C302.621881,8.51828608 303.481409,9.37908014 304.542536,9.37908014 C305.602394,9.37908014 306.463192,8.51828608 306.463192,7.45848649 C306.463192,6.39874208 305.603719,5.53938249 304.542536,5.53938249 C303.481464,5.53927214 302.621881,6.39874208 302.621881,7.45848649 M289.535857,29.0234688 C287.975862,28.9523521 287.12792,28.6925469 286.564502,28.4729071 C285.81747,28.1820953 285.285002,27.8357253 284.724066,27.2761705 C284.164399,26.7164502 283.816814,26.1840401 283.527326,25.4370108 C283.30763,24.8735388 283.047824,24.0257653 282.976762,22.4657761 C282.8993,20.7792222 282.883687,20.2726326 282.883687,15.9999448 C282.883687,11.7272571 282.900459,11.2219364 282.976762,9.53405839 C283.047879,7.97423466 283.308954,7.12773015 283.527326,6.5628237 C283.818139,5.81584955 284.16451,5.28338425 284.724066,4.72245019 C285.283734,4.16284026 285.816201,3.81531162 286.564502,3.52576879 C287.127975,3.30612892 287.975862,3.04637899 289.535857,2.97520707 C291.222472,2.89774553 291.729118,2.88218702 296.000717,2.88218702 C300.273585,2.88218702 300.778908,2.89895932 302.466902,2.97520707 C304.026786,3.04632382 304.873459,3.30739788 305.438147,3.52576879 C306.185179,3.81531162 306.717701,4.1629506 307.278582,4.72245019 C307.838249,5.28217047 308.18462,5.81584955 308.475378,6.5628237 C308.695018,7.12635085 308.954824,7.97412432 309.025941,9.53405839 C309.103458,11.2220467 309.119017,11.7272571 309.119017,15.9999448 C309.119017,20.2713085 309.103569,20.7779533 309.025941,22.4657761 C308.954824,24.025655 308.693749,24.8735388 308.475378,25.4370108 C308.184565,26.1840401 307.838138,26.7164502 307.278582,27.2761705 C306.718915,27.8357804 306.185179,28.1821505 305.438147,28.4729071 C304.874618,28.6924366 304.026786,28.9522417 302.466902,29.0234688 C300.780232,29.1009855 300.273585,29.116544 296.000717,29.116544 C291.729118,29.1164337 291.222472,29.1009855 289.535857,29.0234688 M289.40394,0.0969924345 C287.700498,0.174509141 286.537302,0.444686588 285.520091,0.840104551 C284.468012,1.24848794 283.576264,1.79662209 282.685729,2.68566424 C281.796518,3.57481673 281.248547,4.46661747 280.840107,5.51990676 C280.444633,6.53705837 280.17451,7.70025034 280.096938,9.40357653 C280.018096,11.109551 280,11.6549265 280,16 C280,20.3450735 280.018096,20.890449 280.096993,22.5964235 C280.17451,24.2998048 280.444688,25.4629968 280.840163,26.4801484 C281.248603,27.5320584 281.795304,28.4252384 282.685784,29.3143909 C283.574995,30.2034331 284.466743,30.7501879 285.520146,31.1598954 C286.538571,31.5553686 287.700498,31.8254357 289.403995,31.9030627 C291.111355,31.9805794 291.655518,32 296.000662,32 C300.347185,32 300.891293,31.9819587 302.597384,31.9030627 C304.300827,31.825546 305.464023,31.5553686 306.481178,31.1598954 C307.533257,30.7501879 308.425061,30.2034331 309.31554,29.3143909 C310.204751,28.4252384 310.751508,27.5320584 311.161217,26.4801484 C311.556691,25.4629968 311.828083,24.2998048 311.904387,22.5964235 C311.981904,20.8892352 312,20.3450735 312,16 C312,11.6549265 311.981904,11.109551 311.904387,9.40352136 C311.826814,7.70019517 311.556691,6.53694802 311.161217,5.51985159 C310.751508,4.46788643 310.204751,3.57614085 309.31554,2.68560906 C308.426385,1.79656692 307.533257,1.24843277 306.482447,0.840049379 C305.464023,0.444631416 304.300827,0.173185013 302.598598,0.0969372625 C300.892562,0.0194205557 300.347075,0 296.001986,0 C291.655463,5.51720333e-05 291.111355,0.018151599 289.40394,0.0969924345" id="Shape"></path>
                                    </g>
                                  </g>
                                </g>
                              </g>
                            </g>
                          </g>
                        </svg></a></li>
                    <li><a href="http://feeds.feedburner.com/iobyblog"><svg width="26" height="26" xmlns="http://www.w3.org/2000/svg"><path d="M3.681 18.923c.972 0 1.79.333 2.455 1 .664.667.997 1.487.997 2.462 0 .974-.333 1.782-.997 2.423-.665.64-1.483.961-2.455.961-.971 0-1.79-.32-2.454-.961-.665-.641-.997-1.449-.997-2.423 0-.975.332-1.795.997-2.462.665-.667 1.483-1 2.454-1zM2.608 9.385c3.886 0 7.196 1.359 9.932 4.077 2.735 2.718 4.129 6.025 4.18 9.923v1.384c-.102.667-.512 1.052-1.227 1.154V26h-2.378c-.818 0-1.278-.41-1.38-1.23v-1.385c-.052-2.513-.96-4.654-2.723-6.423-1.764-1.77-3.899-2.654-6.404-2.654H1.227C.511 14.205.102 13.82 0 13.154v-2.462c0-.769.409-1.205 1.227-1.307h1.381zm23.392 14v1.384c-.102.667-.486 1.052-1.15 1.154V26h-2.455c-.767 0-1.201-.41-1.304-1.23v-1.385c-.05-5.077-1.879-9.41-5.483-13C12.003 6.795 7.67 5 2.608 5H1.227C.511 4.897.102 4.487 0 3.77V1.384C0 .615.409.179 1.227.077V0h1.381c6.442 0 11.939 2.282 16.49 6.846 4.55 4.564 6.85 10.077 6.902 16.539z" fill="#3DA9E0" fill-rule="evenodd"/></svg></a></li>
                  </ul>
                </nav>
              </div><!--/.page-footer__follow-->

            </div><!--/.page-footer__right-->

          </div><!--/.page-footer__bottom-->

          <div class="page-footer__far-bottom">
            <div class="page-footer__logo">
              <svg width="80" height="44" xmlns="http://www.w3.org/2000/svg"><g fill="#E88124" fill-rule="evenodd"><path d="M22.263 11.632a11.604 11.604 0 0 0-1.301 0 11.251 11.251 0 0 0-7.182 3.14l7.823 7.828c.094.094.113.229.042.299-.07.07-.198.046-.285-.055 0 0-3.257-3.794-9.08-6.295a11.24 11.24 0 0 0-1.957 6.355c0 6.234 5.055 11.29 11.29 11.29 6.236 0 11.29-5.056 11.29-11.29 0-6.019-4.707-10.935-10.64-11.272M72.603 11.613v10.856c0 .13-.082.238-.181.238-.1 0-.174-.107-.164-.237 0 0 .378-4.98-1.961-10.857H57.419v11.125c0 5.084 3.462 9.372 8.19 10.7l-4.401 5.972 5.896 4.461 10.714-14.547A10.958 10.958 0 0 0 80 22.75V11.613h-7.397zM45.488 11.55H43.21c-2.34 5.987-1.963 11.06-1.963 11.06.01.132-.063.24-.163.24s-.18-.108-.18-.241V.645h-7.355v33.527l11.29.022c6.236 0 11.291-5.074 11.291-11.332 0-6.04-4.708-10.975-10.64-11.313M10.968 5.162C10.968 2.31 8.513 0 5.484 0S0 2.311 0 5.162c0 2.85 2.455 5.16 5.484 5.16.614 0 1.205-.096 1.756-.271A5.557 5.557 0 0 0 9.206 8.95c1.083-.943 1.762-2.29 1.762-3.788M1.29 34.194h7.097V11.613H1.29z"/></g></svg>        </div>
            <div class="page-footer__fine-print">
              <p>copyright © <?php print date('Y'); ?> ioby, a 501(c)(3) nonprofit</p>
              <?php print $privacy_menu; ?>
            </div>
          </div><!--/.page-footer__far-bottom-->
        </div><!--/.page-footer__bottom-area-inside-->
      </div><!--/.page-footer__bottom-area-->
    </footer><!--/.page-footer-->

<!--    <footer id="footer" role="contentinfo">-->
<!--  <!--<div id="bottom">-->
<!--    <div class="full">-->
<!--      <ul>-->
<!--        <li class="title">--><?php ///*print t("get our newsletter") */?><!--</li>-->
<!--        <li class="newsletter">-->
<!--          <!-- Begin MailChimp Signup Form -->
<!--          <div id="mc_embed_signup">-->
<!--            <form action="//ioby.us1.list-manage.com/subscribe/post?u=f3c712aa320de5a6d109211a6&amp;id=71207383ff" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank">-->
<!--              <div class="mc-field-group">-->
<!--                <label for="mce-EMAIL">email address</label>-->
<!--                <input type="text" value="" name="EMAIL" class="required email" id="mce-EMAIL">-->
<!--              </div>-->
<!--              <input type="hidden" value="--><?php ///*$path = drupal_get_destination(); print htmlentities(filter_xss($path['destination'])); */?><!--" name="MMERGE27" class="required" id="mce-MMERGE27">-->
<!--              <input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="btn">-->
<!--            </form>-->
<!--          </div>-->
<!--          <!--End mc_embed_signup-->
<!--        </li>-->
<!--      </ul>-->
<!--    </div>-->
<!--  </div>-->

<!--  <div class="section full clearfix">-->



<!--    --><?php //print theme('links__system_secondary_menu',
//    array('links' => $secondary_menu, //aka "user menu"
//          'attributes' => array('id' => 'secondary-menu',
//                                'class' => array('links', 'clearfix')),
//                                'heading' => t('Secondary menu')
//                                )); ?>

    <!-- /#navigation -->

<!--    --><?php //print render($page['footer']); ?>

  </div><!--/#page -->

  </div><!--/.page-wrap-inside-->


</div><!--/.page-wrapper-->
