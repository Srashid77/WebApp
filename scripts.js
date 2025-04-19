document.addEventListener('DOMContentLoaded', function() {
  // Set default date time to now
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  document.getElementById('date').value = now.toISOString().slice(0, 16);
  
  // Handle form submission
  document.getElementById('healthDataForm').addEventListener('submit', function(event) {
      event.preventDefault();
      
      // Get form values
      const name = document.getElementById('name').value;
      const age = document.getElementById('age').value;
      const systolic = document.getElementById('systolic').value;
      const diastolic = document.getElementById('diastolic').value;
      const bloodPressure = `${systolic}/${diastolic}`;
      const bloodSugar = document.getElementById('bloodSugar').value;
      const meal = document.getElementById('meal').value;
      const date = new Date(document.getElementById('date').value);
      const formattedDate = date.toLocaleString();
      
      // Add to table
      const table = document.getElementById('healthDataBody');
      const newRow = table.insertRow(0); // Add at the top
      
      newRow.innerHTML = `
          <td>${name}</td>
          <td>${age}</td>
          <td>${bloodPressure}</td>
          <td>${bloodSugar}</td>
          <td>${meal}</td>
          <td>${formattedDate}</td>
      `;
      
      // Add highlight effect for new data
      newRow.classList.add('highlight-new');
      setTimeout(() => {
          newRow.classList.remove('highlight-new');
      }, 1500);
      
      // Clear form or reset values as needed
      document.getElementById('healthDataForm').reset();
      
      // Reset default date time
      document.getElementById('date').value = now.toISOString().slice(0, 16);
      
      // Save to localStorage
      saveHealthData(name, age, bloodPressure, bloodSugar, meal, formattedDate);
  });
  
  // Load any existing data from localStorage
  loadHealthData();
  
  // Function to save data to localStorage
  function saveHealthData(name, age, bloodPressure, bloodSugar, meal, date) {
      let healthData = [];
      
      // Get existing data
      const existingData = localStorage.getItem('healthData');
      if (existingData) {
          healthData = JSON.parse(existingData);
      }
      
      // Add new data
      healthData.push({
          name,
          age,
          bloodPressure,
          bloodSugar,
          meal,
          date
      });
      
      // Save back to localStorage
      localStorage.setItem('healthData', JSON.stringify(healthData));
  }
  
  // Function to load data from localStorage
  function loadHealthData() {
      const existingData = localStorage.getItem('healthData');
      if (existingData) {
          const healthData = JSON.parse(existingData);
          const table = document.getElementById('healthDataBody');
          
          // Display most recent entries first
          healthData.reverse().forEach(item => {
              const newRow = table.insertRow();
              newRow.innerHTML = `
                  <td>${item.name}</td>
                  <td>${item.age}</td>
                  <td>${item.bloodPressure}</td>
                  <td>${item.bloodSugar}</td>
                  <td>${item.meal}</td>
                  <td>${item.date}</td>
              `;
          });
      }
  }
});

//ai
function analyzeData() {
    let selectedRow = document.querySelector("input[name='selectedRow']:checked");
    
    if (!selectedRow) {
        alert("Please select a row first!");
        return;
    }

    let healthData = JSON.parse(selectedRow.value);

    fetch("ai_analyze.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(healthData)
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("analysisResult").innerText = data.analysis;
    })
    .catch(error => {
        console.error("Error:", error);
        document.getElementById("analysisResult").innerText = "Error in AI analysis.";
    });
}
//diet
document.getElementById("saveDataButton").addEventListener("click", function () {
    const formData = new FormData(document.getElementById("dietForm"));

    fetch("save_data.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Show response message from server
    })
    .catch(error => {
        console.error("Error:", error);
    });
});

