const canvas = document.getElementById("gameCanvas");
const ctx = canvas.getContext("2d");
canvas.width = 800;
canvas.height = 600;

const scoreElement = document.getElementById('score');
const livesElement = document.getElementById('lives');
const playerNameElement = document.getElementById('player-name');

let playerName = "Névtelen Játékos"; // Alapértelmezett név, ha a backend nem ad vissza adatot

// Képek betöltése
const stickmanImage = new Image();
stickmanImage.src = "stickman.png";

const palinkaImage = new Image();
palinkaImage.src = "palinka.png";

// Játékos adatok
let player = {
    x: canvas.width / 2 - 25,
    y: canvas.height - 100,
    width: 50,
    height: 50,
    speed: 10,
    direction: "right",
    lives: 3,
    isVisible: true,
    invulnerable: false
};

let bottle = {
    x: Math.random() * (canvas.width - 40),
    y: Math.random() * (canvas.height / 2 - 40),
    width: 40,
    height: 40,
    speedX: Math.random() > 0.5 ? 3 : -3,
    speedY: Math.random() > 0.5 ? 3 : -3
};

let obstacles = [];
for (let i = 0; i < 5; i++) {
    obstacles.push({
        x: Math.random() * (canvas.width - 60),  // Akadály bárhol megjelenhet vízszintesen
        y: Math.random() * (canvas.height - 60), // Akadály bárhol megjelenhet függőlegesen (NEM CSAK A FELÉIG)
        width: 60,
        height: 60,
        speedX: Math.random() > 0.5 ? 3 : 7,
        speedY: Math.random() > 0.5 ? 3: 7,
    });
}


let score = 0;
let running = true;

// Billentyűk
let keys = {};
window.addEventListener("keydown", (e) => keys[e.key] = true);
window.addEventListener("keyup", (e) => keys[e.key] = false);

// Ütközésdetektálás
function isColliding(obj1, obj2) {
    return obj1.x < obj2.x + obj2.width &&
           obj1.x + obj1.width > obj2.x &&
           obj1.y < obj2.y + obj2.height &&
           obj1.y + obj1.height > obj2.y;
}

// Játékos nevének lekérése
async function fetchPlayerName() {
    let storedName = localStorage.getItem("felhasznaloNev");
    if (storedName) {
        playerName = storedName;
    } else {
        playerName = "Névtelen Játékos";
    }
    playerNameElement.textContent = `Játékos: ${playerName}`;
}

async function logout() {
    localStorage.removeItem("felhasznaloNev");
    window.location.href = "logout.php";
}

document.addEventListener("DOMContentLoaded", function () {
    fetchPlayerName();
});


// Pontszám frissítése szerveren
async function updateScore(username, score) {
    try {
        const response = await fetch('updateScore.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, score })
        });

        const data = await response.json();
        if (data.status !== "success") {
            console.error("Hiba a pontszám mentésekor:", data.message);
        } else {
            console.log("Pontszám sikeresen mentve:", score);
        }
    } catch (error) {
        console.error("Nem sikerült elküldeni a pontszámot:", error);
    }
}

// Játék ciklus
function gameLoop() {
    if (!running) {
        gameOver();
        return;
    }

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Játékos mozgása
    if (keys["a"] && player.x > 0) {
        player.x -= player.speed;
        player.direction = "left";
    }
    if (keys["d"] && player.x < canvas.width - player.width) {
        player.x += player.speed;
        player.direction = "right";
    }
    if (keys["w"] && player.y > 0) player.y -= player.speed;
    if (keys["s"] && player.y < canvas.height - player.height) player.y += player.speed;

    // Palack mozgása
    bottle.x += bottle.speedX;
    bottle.y += bottle.speedY;
    if (bottle.x <= 0 || bottle.x >= canvas.width - bottle.width) bottle.speedX *= -1;
    if (bottle.y <= 0 || bottle.y >= canvas.height - bottle.height) bottle.speedY *= -1;
// Akadályok mozgása (TELJES pályán)
for (let obstacle of obstacles) {
    obstacle.x += obstacle.speedX;
    obstacle.y += obstacle.speedY;

    // Ha akadály eléri a pálya szélét, pattogjon vissza
    if (obstacle.x <= 0 || obstacle.x >= canvas.width - obstacle.width) {
        obstacle.speedX *= -1;
    }
    if (obstacle.y <= 0 || obstacle.y >= canvas.height - obstacle.height) {
        obstacle.speedY *= -1;
    }
}
    // Ütközések kezelése
    if (isColliding(player, bottle)) {
        score++;
        scoreElement.textContent = `Pontszám: ${score}`;
        bottle.x = Math.random() * (canvas.width - bottle.width);
        bottle.y = Math.random() * (canvas.height / 2 - bottle.height);

    }

    for (let obstacle of obstacles) {
        if (isColliding(player, obstacle) && !player.invulnerable) {
            player.lives--;
            livesElement.textContent = `Életek: ${player.lives}`;
            player.x = canvas.width / 2 - player.width / 2;
            player.y = canvas.height - 100;
            player.invulnerable = true;
            let flashInterval = setInterval(() => {
                player.isVisible = !player.isVisible;
            }, 200);

            setTimeout(() => {
                clearInterval(flashInterval);
                player.isVisible = true;
                player.invulnerable = false;
            }, 3000);

            if (player.lives <= 0) {
                running = false;
            }
        }
    }

    // Játékos kirajzolása
    if (player.isVisible) {
        if (player.direction === "left") {
            ctx.drawImage(stickmanImage, player.x, player.y, player.width, player.height);
        } else if (player.direction === "right") {
            ctx.save();
            ctx.scale(-1, 1);
            ctx.drawImage(stickmanImage, -player.x - player.width, player.y, player.width, player.height);
            ctx.restore();
        }
    }

    // Palack kirajzolása
    ctx.drawImage(palinkaImage, bottle.x, bottle.y, bottle.width, bottle.height);

    // Akadályok kirajzolása
    ctx.fillStyle = "red";
    for (let obstacle of obstacles) {
        ctx.fillRect(obstacle.x, obstacle.y, obstacle.width, obstacle.height);
    }

    requestAnimationFrame(gameLoop);
}


// Felugró ablak az újrajátszáshoz
function showGameOverPopup() {
    const popup = document.createElement("div");
    popup.id = "gameOverPopup";
    popup.innerHTML = `
        <div class="popup-content">
            <h2>Játék vége!</h2>
            <p>Elért pontszámod: ${score}</p>
            <button id="restartGame">Újra játszom</button>
            <button id="exitGame">Kilépés</button>
        </div>
    `;
    document.body.appendChild(popup);

    document.getElementById("restartGame").addEventListener("click", restartGame);
    document.getElementById("exitGame").addEventListener("click", () => popup.remove());
}



// Játék újraindítása
async function restartGame() {
    const { canPlay } = await checkIfCanPlay(playerName);

    document.getElementById("gameOverPopup").remove();

    if (!canPlay) {
        return; // Ha nem játszhat, kilépünk, a popup már megjelenik a checkIfCanPlay-ben
    }

    score = 0;
    player.lives = 3;
    running = true;
    
    player.invulnerable = true;
    setTimeout(() => {
        player.invulnerable = false;
    }, 3000);
    gameLoop();
}
// Leaderboard betöltése és megjelenítése
async function fetchLeaderboard() {
    try {
        const response = await fetch('getLeaderboard.php');
        const leaderboard = await response.json();

        let leaderboardHTML = "<h2>Leaderboard</h2><ol>";
        leaderboard.forEach((player, index) => {
            leaderboardHTML += `<li>${index + 1}. ${player.username}: ${player.points} pont</li>`;
        });
        leaderboardHTML += "</ol>";

        document.getElementById("leaderboard").innerHTML = leaderboardHTML;
    } catch (error) {
        console.error("Hiba a ranglista betöltésekor:", error);
    }
}


function gameOver() {
    ctx.fillStyle = "black";
    ctx.font = "40px Arial";
    ctx.textAlign = "center";
    ctx.fillText("Játék vége", canvas.width / 2, canvas.height / 2);
    ctx.font = "20px Arial";
    ctx.fillText(`Pontszám: ${score}`, canvas.width / 2, canvas.height / 2 + 40);

    // Pontszám mentése és leaderboard frissítése
    updateScore(playerName, score).then(fetchLeaderboard);

    // Megjelenítjük a popupot
    showGameOverPopup();
}

function showCountdownPopup(nextPlayTime) {
    const existingPopup = document.getElementById("countdownPopup");
    if (existingPopup) existingPopup.remove();

    const popup = document.createElement("div");
    popup.id = "countdownPopup";
    popup.innerHTML = `
        <div class="popup-content">
            <h2>Már játszottál ezen a héten!</h2>
            <p>Következő játékig hátralévő idő:</p>
            <p id="countdownTimer"></p>
            <button id="closeCountdown">Bezárás</button>
        </div>
    `;
    document.body.appendChild(popup);

    // Középre igazítás JavaScripttel
    const popupWidth = 300; // A CSS-ben megadott szélesség
    const popupHeight = popup.offsetHeight; // A popup magassága dinamikusan
    popup.style.position = "fixed";
    popup.style.left = `${(window.innerWidth - popupWidth) / 2}px`;
    popup.style.top = `${(window.innerHeight - popupHeight) / 2}px`;

    function updateCountdown() {
        const now = new Date().getTime();
        const nextTime = new Date(nextPlayTime).getTime();
        const timeLeft = nextTime - now;

        if (timeLeft <= 0) {
            document.getElementById("countdownTimer").textContent = "Most már játszhatsz!";
            return;
        }

        const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

        document.getElementById("countdownTimer").textContent = `${days} nap, ${hours} óra, ${minutes} perc, ${seconds} mp`;
    }

    updateCountdown();
    const countdownInterval = setInterval(updateCountdown, 1000);

    document.getElementById("closeCountdown").addEventListener("click", () => {
        clearInterval(countdownInterval);
        popup.remove();
    });
}


// Játékengedély ellenőrzése
async function checkIfCanPlay(username) {
    try {
        const response = await fetch('checkCanPlay.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username })
        });
        
        const data = await response.json();
        
        if (data.canPlay === false) {
            if (data.nextPlayTime) {
                showCountdownPopup(data.nextPlayTime);
            } else {
                alert(`${data.message}\nÚjra játszhatsz egy hét múlva!`);
            }
            return { canPlay: false, isAdmin: data.isAdmin };
        }
        
        return { canPlay: true, isAdmin: data.isAdmin };
    } catch (error) {
        console.error("Hiba a szerverrel való kommunikáció során:", error);
        return { canPlay: false, isAdmin: false };
    }
}

// Pontszám frissítése szerveren
async function updateScore(username, score) {
    try {
        const response = await fetch('updateScore.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, score })
        });

        const data = await response.json();
        if (data.status !== "success") {
            console.error("Hiba a pontszám mentésekor:", data.message);
        } else {
            console.log("Pontszám sikeresen mentve:", score);
        }
    } catch (error) {
        console.error("Nem sikerült elküldeni a pontszámot:", error);
    }
}

// Játék indítása
async function startGame() {
    await fetchPlayerName();
    const { canPlay } = await checkIfCanPlay(playerName);
    
    if (!canPlay) {
        return; // Ha nem játszhat, kilépünk, a popup már megjelenik a checkIfCanPlay-ben
    }
    
    await fetchLeaderboard();

    player.invulnerable = true;
    setTimeout(() => {
        player.invulnerable = false;
    }, 3000);

    gameLoop();
}

document.addEventListener("DOMContentLoaded", function() {
    const startButton = document.getElementById("startGameButton");
    if (startButton) {
        startButton.addEventListener("click", startGame);
    }

    checkIfCanPlay(playerName); // Betöltéskor ellenőrizzük, hogy játszhat-e
});

startGame();



