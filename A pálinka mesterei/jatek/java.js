const canvas = document.getElementById("gameCanvas");
const ctx = canvas.getContext("2d");
canvas.width = 800;
canvas.height = 600;

const scoreElement = document.getElementById('score');
const livesElement = document.getElementById('lives');
const playerNameElement = document.getElementById('player-name');

let playerName = "Névtelen Játékos";

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

let score = 0;
let running = true;

// Billentyűk figyelése
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
    try {
        const response = await fetch('getPlayerName.php', {
            method: 'GET',
            credentials: 'include'
        });

        if (response.ok) {
            const data = await response.json();
            if (data.status === "success") {
                playerName = data.username;
                playerNameElement.textContent = `Játékos: ${playerName}`;
                console.log(`Üdvözlünk, ${playerName}!`);
            } else {
                console.error(data.message);
            }
        } else {
            console.error('Hiba történt a név lekérésekor:', response.status);
        }
    } catch (error) {
        console.error('Hiba a név lekérése során:', error);
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
    if (keys["a"] && player.x > 0) player.x -= player.speed;
    if (keys["d"] && player.x < canvas.width - player.width) player.x += player.speed;
    if (keys["w"] && player.y > 0) player.y -= player.speed;
    if (keys["s"] && player.y < canvas.height - player.height) player.y += player.speed;

    // Palack mozgása
    bottle.x += bottle.speedX;
    bottle.y += bottle.speedY;
    if (bottle.x <= 0 || bottle.x >= canvas.width - bottle.width) bottle.speedX *= -1;
    if (bottle.y <= 0 || bottle.y >= canvas.height - bottle.height) bottle.speedY *= -1;

    // Pontszám növelése
    if (isColliding(player, bottle)) {
        score++;
        scoreElement.textContent = `Pontszám: ${score}`;
        bottle.x = Math.random() * (canvas.width - bottle.width);
        bottle.y = Math.random() * (canvas.height / 2 - bottle.height);
    }

    requestAnimationFrame(gameLoop);
}

// Játék vége
function gameOver() {
    ctx.fillStyle = "black";
    ctx.font = "40px Arial";
    ctx.textAlign = "center";
    ctx.fillText("Játék vége", canvas.width / 2, canvas.height / 2);
    ctx.font = "20px Arial";
    ctx.fillText(`Pontszám: ${score}`, canvas.width / 2, canvas.height / 2 + 40);

    // Pontszám mentése
    console.log("Pontszám mentése:", score);
    saveScore(score);
}

// Pontszám mentése az adatbázisba
async function saveScore(score) {
    if (score > 0) {
        try {
            const response = await fetch('saveScore.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `score=${encodeURIComponent(score)}`,
                credentials: 'include' // Session cookie küldése
            });

            const data = await response.json();
            if (data.status === "success") {
                console.log("Pontszám sikeresen mentve!");
            } else {
                console.error("Hiba a pontszám mentésekor:", data.message);
            }
        } catch (error) {
            console.error("Hálózati hiba a pontszám mentésekor:", error);
        }
    } else {
        console.log("Nem menthető pontszám: 0");
    }
}

// Játék indítása
async function startGame() {
    await fetchPlayerName();
    gameLoop();
}

startGame();
