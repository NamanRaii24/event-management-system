document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded, starting loadEvents');
    updateMonthYearDisplay();
    loadEvents();

    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener("submit", function(event) {
            event.preventDefault();
            let name = document.getElementById("name").value.trim();
            let email = document.getElementById("email").value.trim();
            let subject = document.getElementById("subject").value.trim();
            let message = document.getElementById("message").value.trim();
            let formResponse = document.getElementById("formResponse");

            if (name === "" || email === "" || subject === "" || message === "") {
                formResponse.innerHTML = "Please fill out all fields.";
                formResponse.style.color = "red";
                return;
            }

            formResponse.innerHTML = "Thank you! Your message has been sent.";
            formResponse.style.color = "green";
            contactForm.reset();
        });
    }

    const spinBtn = document.getElementById("spin-btn");
    if (spinBtn) {
        spinBtn.addEventListener("click", function () {
            const rewards = ["🎫 Event Bonus Pass", "🌟 XP Boost", "🎁 Surprise Gift"];
            let result = rewards[Math.floor(Math.random() * rewards.length)];
            let wheel = document.getElementById("wheel");
            let randomDegree = 1800 + Math.floor(Math.random() * 360);
            wheel.style.transform = `rotate(${randomDegree}deg)`;
            setTimeout(() => {
                document.getElementById("spin-result").innerText = "You Won: " + result;
            }, 3000);
        });
    }
});

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function loadEvents(category = '') {
    console.log('Fetching events with category:', category);
    const fetchUrl = './fetch_events.php?category=' + encodeURIComponent(category);
    console.log('Fetch URL:', fetchUrl);
    fetch(fetchUrl)
        .then(response => {
            console.log('Fetch response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(events => {
            console.log('Events received:', events);
            loadEventCards(events);
            loadCalendar(events);
        })
        .catch(error => {
            console.error('Fetch error:', error);
        });
}

function loadEventCards(events) {
    console.log('Loading event cards with events:', events);
    const ongoingList = document.getElementById('ongoing-event-list');
    const upcomingList = document.getElementById('upcoming-event-list');
    
    if (!ongoingList || !upcomingList) {
        console.error('Event list elements not found');
        return;
    }

    ongoingList.innerHTML = '';
    upcomingList.innerHTML = '';

    const today = new Date();
    const ongoingEvents = events.filter(event => new Date(event.date) <= today);
    const upcomingEvents = events.filter(event => new Date(event.date) > today);

    if (ongoingEvents.length === 0) {
        ongoingList.innerHTML = '<p class="text-center">No ongoing events found.</p>';
    } else {
        ongoingEvents.forEach(event => {
            const eventDiv = document.createElement('div');
            eventDiv.className = 'event';
            eventDiv.innerHTML = `
                ${event.image ? `<img src="${event.image}" alt="${event.title}" class="event-img">` : `<div class="event-img-placeholder">No Image Available</div>`}
                <h2>${event.title}</h2>
                <p>Date: ${new Date(event.date).toLocaleDateString()}</p>
                <p>Location: ${event.venue}</p>
            `;
            ongoingList.appendChild(eventDiv);
        });
    }

    if (upcomingEvents.length === 0) {
        upcomingList.innerHTML = '<p class="text-center">No upcoming events found.</p>';
    } else {
        upcomingEvents.forEach(event => {
            const eventDiv = document.createElement('div');
            eventDiv.className = 'event';
            eventDiv.innerHTML = `
                ${event.image ? `<img src="${event.image}" alt="${event.title}" class="event-img">` : `<div class="event-img-placeholder">No Image Available</div>`}
                <h2>${event.title}</h2>
                <p>Date: ${new Date(event.date).toLocaleDateString()}</p>
                <p>Location: ${event.venue}</p>
                <a href="event.php?id=${event.id}" class="register-btn">Register Now</a>
            `;
            upcomingList.appendChild(eventDiv);
        });
    }
}

let currentDate = new Date();

function loadCalendar(events) {
    console.log('Loading calendar with events:', events);
    const tbody = document.querySelector('#event-calendar tbody');
    if (!tbody) {
        console.error('Calendar tbody not found');
        return;
    }
    tbody.innerHTML = '';

    const month = currentDate.getMonth();
    const year = currentDate.getFullYear();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const firstDay = new Date(year, month, 1).getDay();

    let rows = [];
    let currentRow = [];
    let dayCounter = 1;

    for (let i = 0; i < firstDay; i++) {
        currentRow.push('');
    }

    while (dayCounter <= daysInMonth) {
        if (currentRow.length === 7) {
            rows.push(currentRow);
            currentRow = [];
        }
        const eventOnDay = events.filter(e => {
            const eventDate = new Date(e.date);
            return eventDate.getDate() === dayCounter && eventDate.getMonth() === month && eventDate.getFullYear() === year;
        });
        let cellContent = `${dayCounter}`;
        if (eventOnDay.length > 0) {
            cellContent += eventOnDay.map(e => `<br><a href="event.php?id=${e.id}" class="badge bg-primary">${e.title}</a>`).join('');
        }
        currentRow.push(cellContent);
        dayCounter++;
    }

    while (currentRow.length < 7) {
        currentRow.push('');
    }
    rows.push(currentRow);

    rows.forEach(row => {
        const tr = document.createElement('tr');
        row.forEach(cell => {
            const td = document.createElement('td');
            td.innerHTML = cell;
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
}

function updateMonthYearDisplay() {
    const monthYear = document.getElementById('calendar-month-year');
    if (monthYear) {
        monthYear.textContent = currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
    }
}

function changeMonth(offset) {
    currentDate.setMonth(currentDate.getMonth() + offset);
    updateMonthYearDisplay();
    loadEvents();
}

function slideLeft(section) {
    const container = document.querySelector(`.${section}-events .event-list`);
    if (container) {
        const eventWidth = container.querySelector(".event").offsetWidth + 20;
        container.scrollLeft -= eventWidth;
    }
}

function slideRight(section) {
    const container = document.querySelector(`.${section}-events .event-list`);
    if (container) {
        const eventWidth = container.querySelector(".event").offsetWidth + 20;
        container.scrollLeft += eventWidth;
    }
}

document.querySelectorAll(".ongoing-events .left-arrow").forEach(btn => {
    btn.addEventListener("click", function () {
        slideLeft("ongoing");
    });
});

document.querySelectorAll(".ongoing-events .right-arrow").forEach(btn => {
    btn.addEventListener("click", function () {
        slideRight("ongoing");
    });
});

document.querySelectorAll(".upcoming-events .left-arrow").forEach(btn => {
    btn.addEventListener("click", function () {
        slideLeft("upcoming");
    });
});

document.querySelectorAll(".upcoming-events .right-arrow").forEach(btn => {
    btn.addEventListener("click", function () {
        slideRight("upcoming");
    });
});