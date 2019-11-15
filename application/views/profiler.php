<div>
	<p>Memory Usage: <?php echo $memory; ?></p>
	<p>Peak Memory: <?php echo $peak_memory; ?></p>
	<p>Total Time: <?php echo $elapsed_time; ?> ms</p>
	<br/>
	<?php
    if (isset($timers)):
        foreach ($timers as $name => $elapsed) {
            echo "<p>{$name}: {$elapsed} ms</p>";
        }
    endif;
    ?>
</div>
