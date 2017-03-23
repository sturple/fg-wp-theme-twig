<?php
	call_user_func(function () {
    function slugify($str){
      return strtolower(preg_replace('/[^a-z]/i', '_', $str, -1));
    }

		add_shortcode('page_gallery',function ($atts, $content)  {
      $id = empty($atts['id']) ? 0: intval($atts['id']);
      $random = false;
			if (is_array(get_post_meta($id,'randomize', false))){
				$random = (get_post_meta($id,'randomize', false)[0] == 'yes');
			}
			$feature_enable = empty($atts['feature']) ? false : ($atts['feature'] == 'true');
			$filter_enable = empty($atts['filters']) ? true : ($atts['filters'] == 'true');
      $images = [];
      $gallery = [
        'filter' =>  [
          'enable' => $filter_enable,
        ],
        'filters' => [],
        'options' => [
          'limit' => 500,
          'random'=> $random,
          'thumbs_per_row' => get_post_meta($id,'thumbs-per-row', '5'),
					'feature' => $feature_enable

        ],
        'images' => &$images

      ];
      $retr = 'Not a valid ID';


      if ($id > 0 ){
        $post = get_post($id);
        if ($post->post_type == 'gallery'){
          foreach (get_post_meta($id,'cp_gallery')[0] as $item){
            if ( (!empty($item['image'][0])) or (!empty($item['youtubeid'])) ){
              $thumb =  (! empty ($item['thumb'][0])) ?  wp_get_attachment_image_src($item['thumb'][0])[0] : null;
              $filter = [];
              foreach ($item['fg-filters'] as $f){
								if (strlen(trim($f)) > 0){
									// this adds the filter to list
	                $slug = slugify($f);
	                $gallery['filters'][$slug] =$f;
	                $filter[] = $slug;
								}

              }
              $images[] = [
                'thumb' => empty($thumb) ? wp_get_attachment_image_src($item['image'][0],'medium')[0] : $thumb,
                'medium'=> wp_get_attachment_image_src($item['image'][0], 'large')[0],
                'large' => wp_get_attachment_image_src($item['image'][0], 'full')[0],
                /*'caption' => (empty($item['caption'])) ? false : $item['caption'],*/
                'title' => get_the_title($item['image'][0]),
                'filters'=> $filter,
                'youtubeid' => (empty($item['youtubeid'])) ? false : $item['youtubeid']
              ];
            }
          }

          try {
            ob_start();
            Timber::render('page-gallery.twig',$gallery);
            $retr=ob_get_contents();
            ob_end_clean();
          } catch (Twig_Error_Loader $e){
            $retr = '<script>console.error("Error Loading twig template '. $t . ' ' .str_replace('"',"'",$e->getMessage()) .'")</script>';
          }
/*
          $retr .= '<pre>';
          $retr .= print_r($gallery, true);
          $retr .= '</pre>';
*/
        }
      }
			return $retr ;
		});
	});
?>