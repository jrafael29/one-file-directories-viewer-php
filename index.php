<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$search_dir = "";

if(isset($argv[1]) && str_starts_with($argv[1], "/")){
  $search_dir = $argv[1];
}
elseif(isset($_SERVER['PATH_INFO']) && str_starts_with($_SERVER['PATH_INFO'], "/")){
  $search_dir = $_SERVER['PATH_INFO'];
}
else{
  $search_dir = "/";
}

// $_COOKIE['current_dir'] = $search_dir;
setcookie('current_dir', $search_dir);

if(is_image_filename($search_dir)){
  $content = file_get_contents($search_dir);
  header('Content-Type: image/png');
  echo $content;
  exit;
}

$E_DIR = false;

if(is_dir($search_dir)){
$E_DIR = true;
}else{
  $E_DIR = false;
}
// if(isset($_COOKIE['current_dir'])){
//   var_dump($_COOKIE);
//   exit;
// }

function listar_diretorio(string $dir) {
  // lista barra
  try {
    if(!is_dir($dir)) return "is not a directory";
    $hd = opendir($dir);
    if($hd){
      while (false !== ($entry = readdir($hd))){
        if($entry === '.' || $entry === '..') continue;
        if(str_starts_with($entry, "wsl")) continue; // nao lista arquivos de wsl do windows
        yield $entry;
      }
    }
  } catch(\Exception $e) {
    var_dump($e->getTrace());
  } finally {
    closedir($hd);
  }
}

function get_file_content(string $filename){

  if(is_dir($filename)) throw new Exception("invalid file"); 

  $c = file_get_contents($filename);

  return $c;
}

function is_image_filename($filename){

  if(str_ends_with($filename,".png")){
    return true;
  }
  if(str_ends_with($filename,".jpeg")){
    return true;
  }
  if(str_ends_with($filename,".jpg")){
    return true;
  }

  return false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>

</head>
<body>
    <?php if($E_DIR): ?>

    <?php foreach(listar_diretorio($search_dir) as $dir): ?>
      <?php
        $fullPath = rtrim($search_dir, '/') . '/' . $dir;
        $isFile = is_file($fullPath);
        $isDir = is_dir($fullPath);
      ?>
      <a href="<?= rtrim($search_dir, "/") . "/" . $dir ?>"> 
      
        <?php if(is_image_filename($dir)): ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-image preview-icon"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
        <?php elseif($isFile): ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file preview-icon"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/></svg>
        <?php else: ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="true" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder preview-icon"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
        <?php endif; ?>

        <?= $isFile ? $dir : $dir ?> 
      </a>
      <br>
    <?php endforeach; ?>
    <?php elseif(is_image_filename($search_dir)): ?>
      <p>Conteudo da imagem:</p>
      <br>
      <img src="<?= htmlspecialchars($search_dir) ?>" alt="" srcset="">
    <?php else: ?>
      <p>Conteudo do arquivo:</p>
      <br>
      <?= get_file_content($search_dir); ?>

    <?php endif; ?>
    <script>
      // não é recomendado usar o var, mas vou usar, foda-se.
      var currentDir = window.location.pathname;
      
      console.log("todos cookies", currentDir);
      console.log('currentDir', currentDir)


      function onChangeDirectory(dir){
        console.log("changing dir to", dir)

        const locDir = `/${dir.replace(/^\/+/, '')}`;
        console.log("indo para o novo diretorio", {currentDir, locDir, dir, location: window.location});
        
        currentDir = joinPaths(currentDir, locDir);

        window.location.href = currentDir;
      }

      // função obtida em: https://www.w3schools.com/js/js_cookies.asp
      function getCookie(cname) {
        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for(let i = 0; i <ca.length; i++) {
          let c = ca[i];
          while (c.charAt(0) == ' ') {
            c = c.substring(1);
          }
          if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
          }
        }
        return "";
      }

      function joinPaths(...paths) {
        return paths
            .join("/")
            .replace(/\/+/g, "/");
      }
    </script>
</body>
</html>
