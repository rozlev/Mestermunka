<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>A pálinka mesterei</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="cs.css">
  <style>
    /* Növeljük a navbar méretét */
    .navbar {
      padding: 1.5rem 1rem !important;
    }
    .navbar-brand {
      font-size: 2rem !important;
    }
    .nav-link {
      font-size: 1.0rem !important;
      padding: 1rem 2rem !important; /* több hely a szövegek között */
    }
    /* Finom hover effekt: áttetsző fehér háttér, a szöveg megmarad látható */
    .nav-link:hover {
      background-color: rgba(255, 255, 255, 0.2) !important;
      color: #fff !important;
    }
  </style>
</head>
<body>
  
<div id="ageVerificationOverlay" class="age-overlay">
  <div class="age-popup">
      <h2>Elmúltál már 18 éves?</h2>
      <p id="ageWarningText" style="color: red; display: none;">Sajnáljuk, de az oldal használatához 18 évesnek kell lenned!</p>
      <button id="ageConfirm">Igen</button>
      <button id="ageDeny">Nem</button>
  </div>
</div>



<?php session_start(); ?>

<nav class="navbar navbar-expand-lg" style="background: linear-gradient(90deg, #811331, #9c1c3a);">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="#">A pálinka mesterei</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Menü megnyitása">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-white" href="webshop/index.html">Webshop</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="palinka_blog/index.html">Pálinka készítés</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="jatek/index.html">Játék</a>
                </li>
                <li class="nav-item" id="felhasznaloNev">
                    <a class="nav-link text-white" href="bejelentkezes/bejelentkezes.html" id="loginLink">Bejelentkezés</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="kosar/index.html">
                        Kosár (<span id="cart-count">0</span>)
                    </a>
                </li>

                <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
                <li class="nav-item">
                     <a class="nav-link text-white" href="bejelentkezes/admin.php">Admin</a>
                  </li>
              <?php endif; ?>             
            </ul>
        </div>
    </div>
</nav>

  
  <!-- Modal for logout (változatlanul) -->
  <div id="logoutModal" class="modal">
    <div class="modal-content">
      <h2>Kijelentkezés</h2>
      <p>Biztosan ki szeretnél jelentkezni?</p>
      <button id="logoutConfirm">Igen</button>
      <button id="logoutCancel">Mégse</button>
    </div>
  </div>
  
  <!-- Welcome Section -->
  <section id="udvozlo">
    <h2>Üdvözöljük a weboldalon!</h2>
    <p>
      Örömmel üdvözöljük a látogatót! Fedezze fel a pálinkafőzés hagyományait és a legfontosabb lépéseket a készítésében. 
      Ismerkedjen meg a falusi élet különleges szokásaival, és tudjon meg többet erről az ősi mesterségről.
    </p>
  </section>
  <div class="main-container"> 
    <!-- Bemutatkozás -->
    <section id="bemutatkozas" class="intro-section">
        <h2>Bemutatkozás</h2>
        <p>
          A csapatunkba mindenki faluról származik, így közösen tapasztaltuk meg a hagyományos falusi életet és a helyi szokásokat. 
          Egyik ilyen szokás, amit már sokszor átélhettünk, a pálinkafőzés. A falvakban egyre inkább háttérbe szorul ez a tevékenység, 
          és mi úgy gondoltuk, hogy fontos megőrizni és továbbadni ezt a hagyományt.
        </p>
        <p>
          A pálinkafőzés iránti érdeklődés egyre inkább csökken, különösen a fiatalok körében, pedig sok faluban még mindig a közösségi 
          események része. Mi úgy véltük, hogy egy ilyen helyszín felkeresése és bemutatása nemcsak a hagyományok ápolása miatt fontos, 
          hanem azért is, hogy felkeltsük az érdeklődést a pálinkafőzés iránt. Lehet, hogy ez a látogatás másokat is inspirálni fog arra, 
          hogy felfedezzék a pálinkakészítés világát, és esetleg ők maguk is kipróbálják.
        </p>
        <p>
          Ezért úgy éreztük, hogy ez egy aktuális téma, amely hozzájárulhat a kulturális örökség megőrzéséhez, és egyúttal újra 
          felélesztheti a régi tradíciókat a fiatalok körében.
        </p>

    </section>

    <div class="carousel-wrapper">
      <div class="carousel-container">
          <div class="carousel-item active">
              <img src="https://getwalls.io/wallpapers/341058/2022--01--funny-spongebob-whatsapp-dps-4k-8k-50k-70k-1181616589-mobile.jpg" alt="Rózsa Levente">
              <div class="carousel-text">
                  <h2>Rózsa Levente</h2>
                  <br><br>
                  <p>Gyerekkorom óta figyelem, hogyan készül a pálinka, és számomra ez nem csupán egy ital, hanem egy örökség. Minden cseppje a családi hagyományainkat hordozza. A legjobban azt szeretem benne, hogy a természet ízét adhatom vissza – a legjobb gyümölcsökből, a legnagyobb odafigyeléssel. A régi főzési technikák megőrzése mellett a modern eljárásokkal is kísérletezem, hogy mindig a legjobb minőséget hozzam ki belőle.</p>
              </div>
          </div>
  
          <div class="carousel-item">
              <img src="https://th.bing.com/th/id/OIP.hRdZvaNuBq-f67BPG1-amAHaRS?rs=1&pid=ImgDetMain" alt="Kovács Bence">
              <div class="carousel-text">
                  <h2>Kovács Bence</h2>
                  <br><br>
                  <p>Engem a pálinkafőzés tudományos oldala ragadott meg. Imádom kísérletezni az erjesztési folyamatokkal és a lepárlási technikákkal, hogy minél tisztább, aromásabb italt kapjak. Az új technológiák, például a réz- és rozsdamentes főzőüstök kombinációja segít abban, hogy még lágyabb és harmonikusabb ízeket érjek el. A legjobban azt szeretem a pálinkában, hogy soha nem lesz két főzet teljesen egyforma – mindig van benne egy kis meglepetés.</p>
              </div>
          </div>
  
          <div class="carousel-item">
              <img src="https://wallpaperaccess.com/full/189095.jpg" alt="Németh Bence">
              <div class="carousel-text">
                  <h2>Németh Bence</h2>
                  <br><br>
                  <p>Számomra a pálinkafőzés nemcsak a technikáról szól, hanem az érzékek játékáról is. A legfontosabb számomra az ízvilág és az illat harmóniája. Hiszek abban, hogy egy igazán jó pálinkát nemcsak inni kell, hanem megélni az aromáit, a gyümölcsök karakterét. Szeretem a különleges, ritka gyümölcsökből készült pálinkákat, és mindig új ízkombinációkat keresek, hogy a fogyasztók valódi élményt kapjanak minden kortyban.</p>
              </div>
          </div>
      </div>
  
      <!-- PONTOK A NAVIGÁCIÓHOZ -->
      <div class="carousel-dots">
          <span class="dot active" data-index="0"></span>
          <span class="dot" data-index="1"></span>
          <span class="dot" data-index="2"></span>
      </div>
  </div>
</div>


  

  
  <!-- Image and Text Section -->
  <section id="images-with-text">
    
    <div class="content-item">
      <img src="img/szollo.jfif" alt="Szőlő kép">
      <p>
        Bevezetjük Önt a pálinkafőzés művészetébe, ahol a szőlő az alapanyagok királya. 
        A gondosan válogatott gyümölcs az alapja a kiváló minőségű pálinkának. 
        Ismerje meg, hogyan befolyásolja az alapanyag minősége az ital aromáját és ízvilágát, és fedezze fel a legjobb szőlőfajtákat!
        A szőlőpálinkák különlegessége abban rejlik, hogy gazdag, gyümölcsös karakterrel bírnak, amely egyedivé teszi őket.
      </p>
    </div>
  
    <div class="content-item">
      <img src="img/palinkaprogram.jpg" alt="Pálinkaprogram">
      <p>
        Ismerje meg a pálinkaprogramokat, amelyek segítségével autentikus élményben lehet része. 
        Programjaink során megismerheti a hagyományos pálinkafőzési eljárásokat, a modern technológia előnyeit és a helyi kultúrát. 
        Részt vehet borkóstolással egybekötött túrákon, amelyek betekintést nyújtanak a pálinkakészítés titkaiba. 
        Ez egy remek lehetőség, hogy közvetlen kapcsolatba kerüljön a mesterséggel, miközben élvezi a finom ízeket.
      </p>
    </div>
  
    <div class="content-item">
      <img src="img/palinka.jpg" alt="Pálinkás üveg">
      <p>
        Fedezze fel a hagyományos pálinkás üvegek mögött rejlő történeteket és titkokat. 
        Az üvegek formája és díszítése évszázadokon át tükrözte a helyi kultúrát és mesterségbeli tudást. 
        A kézműves üvegek ma már igazi műalkotások, amelyek nemcsak tárolóeszközök, hanem a tradíciók hordozói is. 
        Ismerje meg, hogyan készülnek ezek az egyedi darabok, és hogyan őrzik meg a pálinka aromáját és minőségét.
      </p>
    </div>
  </section>
  
  
  <br><br>
  <!-- Footer Section -->
  <footer>
    <div class="footer-container">
      <div class="footer-logo">
        <img src="img/logo.png" alt="A pálinka mesterei logo" id="logo">
      </div>
  
      <div class="footer-contact">
        <h3>Elérhetőség</h3>
        <p>Telefonszám: +36 30 546 5432</p>
        <p>Email: rozlev404@hengersor.hu</p>
        <p>Hely: Ócsa Kossuth Lajos utca 114</p>
      </div>
  
      <div class="image-gallery">
        <div class="image-item">
          <img src="https://th.bing.com/th/id/OIP.hRdZvaNuBq-f67BPG1-amAHaRS?rs=1&pid=ImgDetMain" alt="Kovács Bence">
          <p>Kovács Bence</p>
        </div>
        <div class="image-item">
          <img src="https://th.bing.com/th/id/R.b5bcc9450ca05c4bacc697dafd85df54?rik=y9k%2funtKiBaJEw&pid=ImgRaw&r=0" alt="Rózsa Levente">
          <p>Rózsa Levente</p>
        </div>
        <div class="image-item">
          <img src="https://wallpaperaccess.com/full/189095.jpg" alt="Németh Bence">
          <p>Németh Bence</p>
        </div>
      </div>
    </div>
    <div class="footer-copyright text-center p-3" style="background-color: #811331; color: white;">
      &copy; <span id="year"></span> Pálinka Mesterei. Minden jog fenntartva.
  </div>
  
  </footer>
  
  <!-- Bootstrap JS -->
  <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  <script src="java.js"></script>
  <script src="spony.js"></script>
  <script src="koros.js"></script>
</body> 
</html>
