<?php
/**
 * Template Menu System
 *
 * @version 1.0
 * @package template
 * @subpackage menu
 * @todo
 * Support for class='current'
 * @log
 *      6.17.2014 - Justin - Created
 */
$limberMenuData = $this->c->limber_plugin_LimberMenu->read();
?><div id="menuTopContainer">
    <ul id="menuTop" class="sf-menu" >
    <?php
        $currentParentID = "";
        foreach ($limberMenuData as $limberMenuItem){
            echo '<li><a href="'.$limberMenuItem["limberMenuURL"].'">'.$limberMenuItem["limberMenuName"].'</a>';
            if (count($limberMenuItem["children"]) > 0){
                echo "<ul>";
                foreach ($limberMenuItem["children"] as $limberMenuItemLevelTwo){
                    echo '<li><a href="'.$limberMenuItemLevelTwo["limberMenuURL"].'">'.$limberMenuItemLevelTwo["limberMenuName"].'</a>';
                    if (count($limberMenuItemLevelTwo["children"]) > 0){
                        echo "<ul>";
                        foreach ($limberMenuItemLevelTwo["children"] as $limberMenuItemLevelThree){
                            echo '<li><a href="'.$limberMenuItemLevelThree["limberMenuURL"].'">'.$limberMenuItemLevelThree["limberMenuName"].'</a></li>';
                        }
                        echo "</ul>";
                    }
                    echo '</li>';
                }
                echo "</ul>";
            }
            echo '</li>';
        }
        ?>
    </ul>
    <div class="clear"></div>
</div>