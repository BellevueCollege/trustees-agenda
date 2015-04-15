
<?php

/**
 * The Template for displaying all Single Agenda posts.
 *
 *
 */
 
get_header(); ?>

<div id="content" <?php if( $mayflower_brand == 'branded' )  {?> class="box-shadow"<?php }?>>

  
<?php
          $mayflower_options = mayflower_get_options(); 
        $html = '<div class="row row-padding">'  ;
        $html .= '<div class="col-xs-9 col-sm-9 col-md-9 ';

        
        $current_layout = $mayflower_options['default_layout'];
        if ( $current_layout == 'sidebar-content' )
        { 
          $html .= 'col-md-push-3';
        }
        $html .= '">';

         echo $html;
           
       
            
            if( $mayflower_options['slider_toggle'] == 'true' ) { 
              if ( $mayflower_options['slider_layout'] == 'featured-full' ) { 
                    get_template_part('part-featured-full'); 
                }
            }            

         if( $mayflower_options['slider_toggle'] == 'true' ) { 
           if ( $mayflower_options['slider_layout'] == 'featured-in-content' ) { 
                get_template_part('part-featured-in-content'); 
            } 
        }         

         if ( is_single() ) : 	?>   
               <div class="content-padding">          
             

                       <h1><?php the_title()?></h1><!-- This is title -->
           <?php
                       $value = get_post_meta( get_the_ID(), 'meeting_date', true );

        		//the_title(); 
        		if(!empty($value))
				{					
					$display_date = date('F j, Y', strtotime($value));

					?> <h1> <?php echo $display_date; ?> </h1> <?php
				}
           ?> 
               </div><!--.content-padding-->
               <div class="content-padding">
               <?php
               	$content = $post->post_content;
                if($content=="") : ?>
                    <!-- Don't display empty the_content or surround divs -->
    <?php

                else :     echo $content;
?>                 
                          
       <?php                   
                endif;                 
       		
        	?>                
                    </div><!-- This is content --> <?php   
                get_template_part('part-blogroll'); 
                 
             else: ?>
                    <p><?php _e('Sorry, these aren\'t the bytes you are looking for.'); ?></p>
            <?php 
            endif; 
         ?>
        </div><!-- col-md-9 -->
          
          <?php
            //if ( $current_layout == 'content-sidebar' || $current_layout == 'sidebar-content' ) {
                get_sidebar();
            //} else {};
          ?>
        </div><!-- .row .row-padding -->
        </div>  


<?php wp_reset_postdata(); ?>
 
<?php get_footer(); ?>
