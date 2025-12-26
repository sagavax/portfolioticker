const countries = [
  { name: "New York", timezoneOffset: -4, color: "#FF6B6B" },
  { name: "London", timezoneOffset: 1, color: "#36DA1C" },
  { name: "Tokyo", timezoneOffset: 9, color: "#5563DE" },
  { name: "Sydney", timezoneOffset: 10, color: "#FFBD4C" },
  { name: "Paris", timezoneOffset: 2, color: "#E72AFE" },
  { name: "Dubai", timezoneOffset: 4, color: "#00BFA6" },
  { name: "Los Angeles", timezoneOffset: -7, color: "#FE7318" },
  { name: "Moscow", timezoneOffset: 3, color: "#D72631" },
];

const container = document.getElementById('clocks');

function createCityCard(city) {
  const card = document.createElement('div');
  card.className = 'city-card';
  // Set border color inline
  card.style.border = `2px solid ${city.color}`;

  // Hover effect for background
  card.addEventListener('mouseenter', () => {
    card.style.background = `rgba(255,255,255,0.1)`;
  });
  card.addEventListener('mouseleave', () => {
    card.style.background = `rgba(255,255,255,0.05)`;
  });

  const cityName = document.createElement('div');
  cityName.className = 'city-name';
  cityName.innerText = city.name;

  const clockDiv = document.createElement('div');
  clockDiv.className = 'clock';
  clockDiv.style.color = city.color;

  card.appendChild(cityName);
  card.appendChild(clockDiv);

  function updateTime() {
    const now = new Date();
    const utc = now.getTime() + now.getTimezoneOffset() * 60000;
    const cityTime = new Date(utc + city.timezoneOffset * 3600000);
    const hours = cityTime.getHours().toString().padStart(2, '0');
    const minutes = cityTime.getMinutes().toString().padStart(2, '0');
    const seconds = cityTime.getSeconds().toString().padStart(2, '0');

    fadeClock(clockDiv, `${hours}:${minutes}:${seconds}`, city.color);
  }

  // Initialize
  updateTime();
  setInterval(updateTime, 1000);

  return card;
}

function fadeClock(element, newText, color) {
  element.style.opacity = 0;
  setTimeout(() => {
    element.textContent = newText;
    element.style.color = color;
    element.style.opacity = 1;
  }, 200);
}

// Generate all cards
countries.forEach(c => {
  const cCard = createCityCard(c);
  container.appendChild(cCard);
});