const canvas = document.getElementById("gameCanvas");
const ctx = canvas.getContext("2d");

const scoreElement = document.getElementById('score');
const livesElement = document.getElementById('lives');
const playerNameElement = document.getElementById('player-name');

let playerName = "Névtelen Játékos";
const stickmanImage = new Image();
stickmanImage.src = "stickman.png";
stickmanImage.onerror = () => console.error("Failed to load stickman.png");

const palinkaImage = new Image();
palinkaImage.src = "palinka.png";
palinkaImage.onerror = () => console.error("Failed to load palinka.png");

let player = {
    x: 0,
    y: 0,
    width: 0,
    height: 0,
    speed: 0,
    direction: "right",
    lives: 3,
    isVisible: true,
    invulnerable: false
};

let bottle = {
    x: 0,
    y: 0,
    width: 0,
    height: 0,
    speedX: 0,
    speedY: 0
};

let obstacles = [];
for (let i = 0; i < 5; i++) {
    obstacles.push({
        x: 0,
        y: 0,
        width: 0,
        height: 0,
        speedX: 0,
        speedY: 0
    });
}

let score = 0;
let running = false;
let gameStarted = false;
let gameOverTriggered = false;

function resizeCanvas() {
    const maxWidth = 1000;
    const maxHeight = 750;
    const container = document.querySelector('.container');
    const containerWidth = container ? container.offsetWidth : window.innerWidth;

    canvas.width = Math.min(maxWidth, containerWidth * 0.8);
    canvas.height = canvas.width * (maxHeight / maxWidth);

    window.canvasScale = {
        x: canvas.width / maxWidth,
        y: canvas.height / maxHeight
    };

    player.x = (canvas.width / 2 - 25) * window.canvasScale.x;
    player.y = (canvas.height - 100) * window.canvasScale.y;
    player.width = 80 * window.canvasScale.x;
    player.height = 80 * window.canvasScale.y;
    player.speed = 10 * window.canvasScale.x;

    bottle.width = 60 * window.canvasScale.x;
    bottle.height = 60 * window.canvasScale.y;
    bottle.x = Math.random() * (canvas.width - bottle.width);
    bottle.y = Math.random() * (canvas.height / 2 - bottle.height);
    bottle.speedX = (Math.random() > 0.5 ? 3 : -3) * window.canvasScale.x;
    bottle.speedY = (Math.random() > 0.5 ? 3 : -3) * window.canvasScale.y;

    obstacles.forEach(obstacle => {
        obstacle.width = 90 * window.canvasScale.x;
        obstacle.height = 90 * window.canvasScale.y;
        obstacle.x = Math.random() * (canvas.width - obstacle.width);
        obstacle.y = Math.random() * (canvas.height - obstacle.height);
        obstacle.speedX = (Math.random() > 0.5 ? 3 : 7) * window.canvasScale.x;
        obstacle.speedY = (Math.random() > 0.5 ? 3 : 7) * window.canvasScale.y;
    });
}

if (canvas) {
    resizeCanvas();
    window.addEventListener("resize", resizeCanvas);
}

let keys = {};
window.addEventListener("keydown", (e) => keys[e.key] = true);
window.addEventListener("keyup", (e) => keys[e.key] = false);

function isColliding(obj1, obj2) {
    return obj1.x < obj2.x + obj2.width &&
           obj1.x + obj1.width > obj2.x &&
           obj1.y < obj2.y + obj2.height &&
           obj1.y + obj1.height > obj2.y;
}

async function fetchPlayerName() {
    let storedName = localStorage.getItem("felhasznaloNev");
    if (storedName) {
        playerName = storedName;
    } else {
        playerName = "Névtelen Játékos";
    }
    if (playerNameElement) {
        playerNameElement.textContent = `Játékos: ${playerName}`;
    }
}

async function logout() {
    localStorage.removeItem("felhasznaloNev");
    localStorage.removeItem("currentCoupon");
    window.location.href = "logout.php";
}

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
            return null;
        } else if (data.message === "Admin pont nem mentve.") {
            console.log("Admin felhasználó, pontszám nem mentve:", score);
            return null;
        } else {
            console.log("Pontszám sikeresen mentve:", score, "scoreID:", data.scoreID);
            return data.scoreID;
        }
    } catch (error) {
        console.error("Nem sikerült elküldeni a pontszámot:", error);
        return null;
    }
}

function drawStartButton() {
    const gradient = ctx.createLinearGradient(canvas.width / 2 - 100, 0, canvas.width / 2 + 100, 0);
    gradient.addColorStop(0, "#811331");
    gradient.addColorStop(1, "#9c1c3a");

    ctx.fillStyle = gradient;
    ctx.fillRect(canvas.width / 2 - 100, canvas.height / 2 - 25, 200, 50);

    ctx.strokeStyle = "#fff";
    ctx.lineWidth = 2;
    ctx.strokeRect(canvas.width / 2 - 100, canvas.height / 2 - 25, 200, 50);

    ctx.fillStyle = "#fff";
    ctx.font = "bold 20px Arial, sans-serif";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText("Játék indítása", canvas.width / 2, canvas.height / 2);
}

function isMouseOverButton(x, y) {
    return x > canvas.width / 2 - 100 && x < canvas.width / 2 + 100 &&
           y > canvas.height / 2 - 25 && y < canvas.height / 2 + 25;
}

canvas.addEventListener("click", async (e) => {
    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) * (canvas.width / rect.width);
    const y = (e.clientY - rect.top) * (canvas.height / rect.height);

    if (!gameStarted && isMouseOverButton(x, y)) {
        await startGame();
    }
});

function gameLoop() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (!gameStarted) {
        drawStartButton();
        requestAnimationFrame(gameLoop);
        return;
    }

    if (!running && !gameOverTriggered) {
        gameOverTriggered = true;
        gameOver();
        return;
    }

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

    bottle.x += bottle.speedX;
    bottle.y += bottle.speedY;
    if (bottle.x <= 0 || bottle.x >= canvas.width - bottle.width) bottle.speedX *= -1;
    if (bottle.y <= 0 || bottle.y >= canvas.height - bottle.height) bottle.speedY *= -1;

    for (let obstacle of obstacles) {
        obstacle.x += obstacle.speedX;
        obstacle.y += obstacle.speedY;

        if (obstacle.x <= 0 || obstacle.x >= canvas.width - obstacle.width) {
            obstacle.speedX *= -1;
        }
        if (obstacle.y <= 0 || obstacle.y >= canvas.height - obstacle.height) {
            obstacle.speedY *= -1;
        }
    }

    if (isColliding(player, bottle)) {
        score++;
        scoreElement.textContent = `Pontszám: ${score}`;
        bottle.x = Math.random() * (canvas.width - bottle.width);
        bottle.y = Math.random() * (canvas.height / 2 - bottle.height);
    }

    for (let obstacle of obstacles) {
        if (isColliding(player, obstacle) && !player.invulnerable && running) {
            player.lives--;
            livesElement.textContent = `Életek: ${player.lives}`;
            player.x = canvas.width / 2 - player.width / 2;
            player.y = canvas.height - 100 * window.canvasScale.y;
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
                player.lives = 0;
                livesElement.textContent = `Életek: ${player.lives}`;
                running = false;
            }
        }
    }

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

    ctx.drawImage(palinkaImage, bottle.x, bottle.y, bottle.width, bottle.height);

    ctx.fillStyle = "red";
    for (let obstacle of obstacles) {
        ctx.fillRect(obstacle.x, obstacle.y, obstacle.width, obstacle.height);
    }

    if (running) {
        requestAnimationFrame(gameLoop);
    }
}

async function showGameOverPopup(scoreID) {
    const existingPopup = document.getElementById("gameOverPopup");
    if (existingPopup) existingPopup.remove();

    const popup = document.createElement("div");
    popup.id = "gameOverPopup";
    popup.innerHTML = `
        <div class="popup-content">
            <p>Elért pontszámod: ${score}</p>
            <p>Következő játékig hátralévő idő:</p>
            <p id="countdownTimer">Betöltés...</p>
            <div id="couponSection"></div>
            <button id="exitGame">Kilépés</button>
        </div>
    `;
    document.body.appendChild(popup);

    const nextPlayTime = await getNextPlayTime(playerName);
    updateCountdown(nextPlayTime);

    const couponSection = document.getElementById("couponSection");
    if (score >= 15 && scoreID) {
        const coupon = await fetchCoupon(scoreID);
        if (coupon && !coupon.startsWith("Hiba") && !coupon.includes("Nincs elérhető kupon")) {
            couponSection.innerHTML = `
                <p style="color: #FFD700;">Gratulálunk! Elértél 15 pontot, itt a kuponod:</p>
                <p style="font-weight: bold;">${coupon}</p>
            `;
        } else {
            couponSection.innerHTML = `
                <p style="color: #FF6347;">${coupon || "Hiba a kupon lekérdezésekor!"}</p>
            `;
        }
    } else {
        couponSection.innerHTML = `
            <p style="color: #FF6347;">Nem értél el 15 pontot, így nem kapsz kupont.</p>
        `;
    }

    document.getElementById("exitGame").addEventListener("click", () => popup.remove());
}

async function getNextPlayTime(username) {
    try {
        const response = await fetch('checkCanPlay.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username })
        });
        const data = await response.json();
        return data.nextPlayTime || null;
    } catch (error) {
        console.error("Hiba a következő játék idejének lekérdezésekor:", error);
        return null;
    }
}

function updateCountdown(nextPlayTime) {
    const countdownTimer = document.getElementById("countdownTimer");
    if (!nextPlayTime) {
        countdownTimer.textContent = "Most már játszhatsz!";
        return;
    }

    const interval = setInterval(() => {
        const now = new Date().getTime();
        const nextTime = new Date(nextPlayTime).getTime();
        const timeLeft = nextTime - now;

        if (timeLeft <= 0) {
            countdownTimer.textContent = "Most már játszhatsz!";
            clearInterval(interval);
            return;
        }

        const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

        countdownTimer.textContent = `${days} nap, ${hours} óra, ${minutes} perc, ${seconds} mp`;
    }, 1000);
}

async function fetchCoupon(scoreID) {
    try {
        console.log("Sending fetchCoupon request with:", { username: playerName, scoreID: scoreID });
        const response = await fetch('getCoupon.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username: playerName, scoreID: scoreID })
        });
        const data = await response.json();
        if (data.status === "success") {
            localStorage.setItem("currentCoupon", data.coupon);
            return data.coupon;
        } else {
            return "Hiba a kupon lekérdezésekor: " + data.message;
        }
    } catch (error) {
        console.error("Hiba a kupon lekérdezésekor:", error);
        return "Hiba a kupon lekérdezésekor!";
    }
}

async function restartGame() {
    const { canPlay } = await checkIfCanPlay(playerName);

    document.getElementById("gameOverPopup")?.remove();

    if (!canPlay) {
        return;
    }

    score = 0;
    player.lives = 3;
    running = true;
    gameOverTriggered = false;
    
    player.invulnerable = true;
    setTimeout(() => {
        player.invulnerable = false;
    }, 3000);
    gameLoop();
}

let leaderboardPage = 0;
const itemsPerPage = 3;

async function fetchLeaderboard() {
    try {
        const response = await fetch('getLeaderboard.php');
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const leaderboard = await response.json();
        console.log("Leaderboard data:", leaderboard);

        const leaderboardList = document.getElementById("leaderboard-list");
        const pagination = document.getElementById("pagination");
        leaderboardList.innerHTML = "";

        const start = leaderboardPage * itemsPerPage;
        const end = start + itemsPerPage;
        const paginatedLeaderboard = leaderboard.slice(start, end);

        paginatedLeaderboard.forEach((player, index) => {
            const li = document.createElement("li");
            li.textContent = `${start + index + 1}. ${player.username}: ${player.points} pont`;
            leaderboardList.appendChild(li);
        });

        pagination.innerHTML = "";
        if (leaderboard.length > itemsPerPage) {
            if (leaderboardPage > 0) {
                const prevButton = document.createElement("button");
                prevButton.textContent = "Előző";
                prevButton.addEventListener("click", () => {
                    leaderboardPage--;
                    fetchLeaderboard();
                });
                pagination.appendChild(prevButton);
            }
            if (end < leaderboard.length) {
                const nextButton = document.createElement("button");
                nextButton.textContent = "Következő";
                nextButton.addEventListener("click", () => {
                    leaderboardPage++;
                    fetchLeaderboard();
                });
                pagination.appendChild(nextButton);
            }
        }
    } catch (error) {
        console.error("Hiba a ranglista betöltésekor:", error);
    }
}

async function gameOver() {
    const scoreID = await updateScore(playerName, score);
    await fetchLeaderboard();

    if (score >= 15 && scoreID) {
        showGameOverPopup(scoreID);
    } else {
        showGameOverPopup(null);
    }
}

function showCountdownPopup(nextPlayTime) {
    const existingPopup = document.getElementById("countdownPopup");
    if (existingPopup) existingPopup.remove();

    const popup = document.createElement("div");
    popup.id = "countdownPopup";

    const coupon = localStorage.getItem("currentCoupon");
    let couponHTML = '';
    if (coupon && score >= 15) {
        couponHTML = `<p style="color: #FFD700;">Aktuális kupon: <strong>${coupon}</strong></p>`;
    } else {
        couponHTML = `<p style="color: #FF6347;">Nincs aktív kupon.</p>`;
    }

    popup.innerHTML = `
        <div class="popup-content">
            <p>Következő játékig hátralévő idő:</p>
            <p id="countdownTimer">Betöltés...</p>
            ${couponHTML}
            <button id="closeCountdown">Bezárás</button>
        </div>
    `;

    document.body.appendChild(popup);

    const popupWidth = 300;
    const popupHeight = popup.offsetHeight;
    popup.style.position = "fixed";
    popup.style.left = `${(window.innerWidth - popupWidth) / 2}px`;
    popup.style.top = `${(window.innerHeight - popupHeight) / 2}px`;

    function updateCountdownPopup() {
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

    updateCountdownPopup();
    const countdownInterval = setInterval(updateCountdownPopup, 1000);

    document.getElementById("closeCountdown").addEventListener("click", () => {
        clearInterval(countdownInterval);
        popup.remove();
    });
}

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
        console.log("checkIfCanPlay response:", data);
        
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

async function startGame() {
    await fetchPlayerName();
    const { canPlay } = await checkIfCanPlay(playerName);
    
    if (!canPlay) {
        return;
    }
    
    await fetchLeaderboard();

    gameStarted = true;
    running = true;
    player.invulnerable = true;
    setTimeout(() => {
        player.invulnerable = false;
    }, 3000);
    gameLoop();
}

document.addEventListener("DOMContentLoaded", function() {
    fetchPlayerName();
    if (canvas) {
        checkIfCanPlay(playerName);
        gameLoop();
    }
});