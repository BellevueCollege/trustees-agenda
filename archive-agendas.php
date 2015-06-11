<?php
get_header();
//get_template_part('layout');
global $mayflower_brand; 
$mayflower_options = mayflower_get_options();
$blog_flag = is_active_sidebar('blog-widget-area') ?  '1' : '0';
$page_flag = is_active_sidebar('page-widget-area') ? '1' : '0'; 
//echo "blog:".$blog_flag;
//echo "page:".$page_flag;
//exit();
 
?>

<div id="content" <?php if( $mayflower_brand == 'branded' )  {?> class="box-shadow"<?php }?>>

  
<?php
     if (is_active_sidebar('top-global-widget-area')  || is_active_sidebar('global-widget-area')) 
     { 
        //echo "++++++++++++++++";
        
        $html = '<div class="row row-padding">'  ;
        $html .= '<div class="col-xs-9 col-sm-9 col-md-9 ';
        
        $current_layout = $mayflower_options['default_layout'];
        if ( $current_layout == 'sidebar-content' )
        { 
          $html .= 'col-md-push-3';
        }
        $html .= '">';
         echo $html;
           
        if($page_flag == '1' ) // page check
        {
            //$mayflower_options = mayflower_get_options();
            if( $mayflower_options['slider_toggle'] == 'true' ) { 
              if ( $mayflower_options['slider_layout'] == 'featured-full' ) { 
                    get_template_part('part-featured-full'); 
                }
            }
            
         } // end if page check
        
         if( $mayflower_options['slider_toggle'] == 'true' ) { 
           if ( $mayflower_options['slider_layout'] == 'featured-in-content' ) { 
                get_template_part('part-featured-in-content'); 
            } 
        }  
        if ( is_home() ) {
            // If we are loading the Blog home page (home.php)
            get_template_part('part-home');
        } else if ( is_page_template('page-staff.php') ) {         
            // If we are loading the staff page template
            get_template_part('part-staff');
        } else if ( is_singular('staff') ) {
            // If we are loading the single-staff 
            get_template_part('part-single-staff');
        }else if ( is_page_template('page-nav-page.php') && $blog_flag == '1') {
            // If we are loading the navigation-page page template
           get_template_part('part-nav-page');
        }else if ( is_page_template('page-nav-page.php') && $page_flag == '1' ) {
            // If we are loading the navigation-page page template
            get_template_part('part-nav-page-grid');
        } else if ( is_page_template('page-nav-page-list.php') ) {
            // If we are loading the navigation-page page template
            get_template_part('part-nav-page-list');
        } else if ( is_single() ) {
            // If we are loading the navigation-page page template
            get_template_part('part-single');
        } else if ( is_archive() && $blog_flag == '1' ) {
                      // If we are loading the navigation-page page template
                      get_template_part('part-archive');
                      
         } else {           
            if ( have_posts() ) : while ( have_posts() ) : the_post();   ?>       
               <div class="content-padding <?php
                if ( ($mayflower_options['slider_toggle'] == 'true') && ($mayflower_options['slider_layout'] == 'featured-in-content') )
                { 
                    if($blog_flag == '1')
                    {
                       echo "row-padding";
                    }
                    else if($page_flag == '1' && is_front_page())
                    {
                        echo "top-spacing30";
                    }                  
                       
                } 
                 ?>">          
              <?php      
                if (is_front_page() ) {
                    //don't show the title on the home page
                } else { 
                    
                    if ( is_main_site()) {
                        //if main site, only show title here if page is not top-most ancestor
                        if(intval($post->post_parent)>0){ ?>
                           <h1><?php the_title() ?> </h1><!-- This is title --> <?php
                        }
                    } else { ?>

                       <h1><?php the_title()?></h1><!-- This is title --><?php
                    }
                }; ?>
                
               </div><!--.content-padding-->
               <div class="content-padding">
               <?php
                if($post->post_content=="") : ?>
                    <!-- Don't display empty the_content or surround divs -->
                    <?php
                else :     
                      ?>
                           <?php the_content(); 
                          
                           ?>
                       
                <?php  
                endif; 

                // Display list of agendas              

                foreach(posts_by_year() as $year => $posts) : ?>
                <h2><?php echo $year; ?></h2>
               
                <ul>
                  <?php foreach($posts as $post) : setup_postdata($post); ?>
                    <li>
                      <a href="<?php the_permalink(); ?>">
                        <?php 
                          $value = get_post_meta( get_the_ID(), 'meeting_date', true );
                          //$status = get_post_status(get_the_ID());
                           error_log("value:".$value);
                          //error_log("status:".$status);
                          //the_title(); 
                          if(!empty($value))
                      {         
                        $display_date = date('F j, Y', strtotime($value));
                        //error_log("value is not empty");
                        echo $display_date;
                      }
                        ?>
                      </a>
                      <?php
                      $special_meeting = get_post_meta( get_the_ID(), 'special_meeting', true );          
                        $special = "";
                        if($special_meeting)
                          $special = " (Special Meeting)";
                        echo  $special;
                      ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endforeach; 


                 //require_once('part-archive-agendas.php'); // This displays all the board of meeting agendas                
                   ?>  </div><!-- This is content --> <?php   
                get_template_part('part-blogroll'); 
                 
            endwhile; else: ?>
                    <p><?php _e('Sorry, these aren\'t the bytes you are looking for.'); ?></p>
            <?php 
            endif; 
        } ?>
        </div><!-- col-md-9 -->
          
          <?php
            //if ( $current_layout == 'content-sidebar' || $current_layout == 'sidebar-content' ) {
                get_sidebar();
            //} else {};
          ?>
        </div><!-- .row .row-padding -->  

        <?php 
  } //END IF SIDEBAR HAS CONTENT
  else 
     {
  //SIDEBAR IS EMPTY
      //echo "----------------------------------------------";
          if($page_flag == '1')
          {
            if( $mayflower_options['slider_toggle'] == 'true' )
            { 
              get_template_part('part-featured-full'); 
            }
          }
          $html = '<div class="row-padding';
          if($page_flag == '1')
               $html .= 'row';
          $html .= '">';
          echo $html;
          if ( is_home() ) {
            // If we are loading the Blog home page (home.php)
            get_template_part('part-home');
          } else if ( is_page_template('page-staff.php') ) {
            // If we are loading the staff page template
            get_template_part('part-staff');
          } else if ( is_singular('staff') ) {
            // If we are loading the single-staff 
            get_template_part('part-single-staff');
          } else if(  is_page_template('page-nav-page.php') && $blog_flag == '1') {
             // If we are loading the navigation-page page template
                    get_template_part('part-nav-page');
          } else if ( is_page_template('page-nav-page.php') && $page_flag == '1') {
              // If we are loading the navigation-page page template
              get_template_part('part-nav-page-grid');
          } else if ( is_page_template('page-nav-page-list.php') && $page_flag == '1') {
              // If we are loading the navigation-page page template
              get_template_part('part-nav-page-list');
          } else if ( is_archive() && $blog_flag == '1' ) {
                    // If we are loading the navigation-page page template
                    get_template_part('part-archive');
          } else if ( is_single() ) {
            // If we are loading the navigation-page page template
            get_template_part('part-single');
          } else { 
          
            if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                <div class="content-padding <?php
                if($blog_flag == '1' && ($mayflower_options['slider_toggle'] == 'true') && ($mayflower_options['slider_layout'] == 'featured-in-content') )
                      echo ' row-padding'; ?>
                ">             
                 <?php 
              if (is_front_page() ) {
                  //don't show the title on the home page
              } else { 
                  if ( is_main_site()) {
                      //if main site, only show title here if page is not top-most ancestor
                      if(intval($post->post_parent)>0){ ?>
                          <h1> <?php the_title() ?> </h1> <?php                         
                      }
                  } else { ?>
                          <h1> <?php the_title() ?> </h1>  <?php 
                  }
              }; ?>
              
              </div><!--.content-padding-->
               <div class="content-padding">
               <?php             
              
              if($post->post_content=="") : ?>
                  <!-- Don't display empty the_content or surround divs -->
                  <?php
              else : ?>
                 
                       <?php
                            the_content();
                            
                       ?>
                 
                 <!--.content-padding--> <?php
        
              endif; 
              // Display list of agendas
                foreach(posts_by_year() as $year => $posts) : ?>
                <h2><?php echo $year; ?></h2>

                <ul>
                  <?php foreach($posts as $post) : setup_postdata($post); ?>
                    <li>
                      <a href="<?php the_permalink(); ?>">
                        <?php 
                          $value = get_post_meta( get_the_ID(), 'meeting_date', true );

                          //the_title(); 
                          if(!empty($value))
                      {         
                        $display_date = date('F j, Y', strtotime($value));

                        echo $display_date;
                      }
                        ?>
                      </a>
                      <?php
                      $special_meeting = get_post_meta( get_the_ID(), 'special_meeting', true );          
                        $special = "";
                        if($special_meeting)
                          $special = " (Special Meeting)";
                        echo  $special;
                      ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endforeach; 




              // require_once('part-archive-agendas.php'); // This displays all the board of meeting agendas ?>
              </div> <?php 
                          
              get_template_part('part-blogroll'); ?>
              
      <?php
      endwhile; else: ?>
      <p><?php _e('Sorry, these aren\'t the bytes you are looking for.'); ?></p>
      <?php endif; 
    } ?>
      </div><!--.row-padding-->
<?php
     }//END SIDEBAR IS EMPTY
?>


</div><!-- #content-->

<?php get_footer();?>

<?php

function posts_by_year() {
  // array to use for results
  $years = array();

  // get posts from WP
  $posts = get_posts(array(
    'numberposts' => -1,
    'meta_key'  => 'meeting_date',
    //'orderby' => 'meeting_date',
    'orderby' => 'meta_value',
    'order' => 'DESC',
    'post_type' => 'agendas',
    'post_status' => 'publish'
  ));

  // loop through posts, populating $years arrays
  foreach($posts as $post) {
    if(isset($post->meeting_date) && !empty($post->meeting_date))
          $years[date('Y', strtotime($post->meeting_date))][] = $post;
  }

  // reverse sort by year
  krsort($years);
  //echo $years;
  return $years;
}

