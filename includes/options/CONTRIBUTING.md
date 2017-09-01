Some changes to the redux code when updating

```
//framework.php -> comment line 62
require_once( dirname( __FILE__ ) . '/inc/welcome/welcome.php' ); 
```

```
//framework.php -> comment line 410
    //require_once 'core/dashboard.php';
   //new reduxDashboardWidget( $this );
```

//framework.php -> add line 809
```
do_action( "kleo-opts-saved", $value, $this->transients['changed_values'] );
```

//framework.php
comment block around 418
```
require_once 'core/newsflash.php';

$params = array(
    'dir_name'    => 'notice',
    'server_file' => 'http://reduxframework.com/wp-content/uploads/redux/redux_notice.json',
    'interval'    => 3,
    'cookie_id'   => 'redux_blast',
);

new reduxNewsflash( $this, $params );
$GLOBALS['redux_notice_check'] = 1;
```

&& 1174-1275 comment
```
$this->dev_mode_forced  = true;
$this->args['dev_mode'] = true;
```

//inc/extensions/customizer -> line 516
```
'transport'         => isset($option['customizer_post']) ? 'postMessage' : 'refresh',
```

//image_select/field.php
echo '<img src="' . $v['img'] . '" alt="' . $v['alt'] . '" title="'. $v['alt'] .'" class="' . $v['class'] . '" style="' . $style . '"' . $presets . $merge . ' />';
```