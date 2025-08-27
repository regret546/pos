<?php

session_destroy();

// Simple redirect to login page in the same directory
echo '<script>
	window.location = "index.php";
</script>';