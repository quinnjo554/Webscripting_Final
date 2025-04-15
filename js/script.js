
document.addEventListener('DOMContentLoaded', function () {

  const studentNameSpan = document.getElementById('studentName');
  if (studentNameSpan) {
    studentNameSpan.textContent = sessionStorage.getItem('userName') || 'Demo Student';
  }

  const instructorNameSpan = document.getElementById('instructorName');
  if (instructorNameSpan) {
    instructorNameSpan.textContent = sessionStorage.getItem('userName') || 'Dr. Demo';
  }

  const logoutButton = document.getElementById('logoutButton');
  if (logoutButton) {
    logoutButton.addEventListener('click', function (e) {
      e.preventDefault();
      sessionStorage.removeItem('userName');
      sessionStorage.removeItem('userRole');
      window.location.href = 'login.html';
    });
  }


  //  Student Dashboard Stuff
  //  TODO: Move to another file

  const currentAppointmentDetails = document.getElementById('currentAppointmentDetails');
  const noAppointmentMsg = document.getElementById('noAppointmentMsg');
  const deleteAppointmentBtn = document.getElementById('deleteAppointmentBtn');
  const scheduleNewBtn = document.getElementById('scheduleNewBtn');

  if (currentAppointmentDetails) {
    fetchStudentAppointment();
  }

  if (deleteAppointmentBtn) {
    deleteAppointmentBtn.addEventListener('click', function () {
      if (confirm('Are you sure you want to delete your current appointment?')) {
        console.log('Simulating delete appointment...');
        alert('Appointment deleted successfully.');
        fetchStudentAppointment(); // Refresh the display
      }
    });
  }



  const showBookingsBtn = document.getElementById('showBookingsBtn');
  const viewDateInput = document.getElementById('viewDate');
  const bookingsTable = document.getElementById('bookingsTable');
  const bookingsTableBody = bookingsTable ? bookingsTable.querySelector('tbody') : null;
  const bookedAppointmentsListDiv = document.getElementById('bookedAppointmentsList');
  const noBookingsMsg = document.getElementById('noBookingsMsg');
  const defaultMsg = bookedAppointmentsListDiv ? bookedAppointmentsListDiv.querySelector('p.text-muted') : null;


  if (showBookingsBtn && viewDateInput && bookingsTableBody) {
    viewDateInput.valueAsDate = new Date();

    showBookingsBtn.addEventListener('click', function () {
      const selectedDate = viewDateInput.value;
      if (!selectedDate) {
        alert('Please select a date.');
        return;
      }
      fetchInstructorBookings(selectedDate);
    });
  }

  // --- Helper Functions (Simulated Data) ---

  function fetchStudentAppointment() {
    console.log('Simulating fetch student appointment...');
    // TEMP, while waiting for backend api
    // TODO: use fetch('/api/student/appointment')

    const hasAppointment = Math.random() > 0.3; // Simulate sometimes having an appointment
    let appointmentData = null;
    if (hasAppointment) {
      appointmentData = {
        date: '2023-12-15',
        time: '10:00',
        instructor: 'Dr. Smith',
        project: 'Final Year Project Presentation',
        group: 'Alice, Bob'
      };
    }

    displayStudentAppointment(appointmentData);
  }

  function displayStudentAppointment(data) {
    const detailsDiv = document.getElementById('currentAppointmentDetails');
    const noMsg = document.getElementById('noAppointmentMsg');
    const deleteBtn = document.getElementById('deleteAppointmentBtn');
    const scheduleBtn = document.getElementById('scheduleNewBtn');

    if (!detailsDiv || !noMsg || !deleteBtn || !scheduleBtn) return;

    detailsDiv.querySelectorAll('p:not(#noAppointmentMsg)').forEach(p => p.classList.add('d-none'));
    deleteBtn.classList.add('d-none');


    if (data) {
      document.getElementById('appt-date').textContent = data.date;
      document.getElementById('appt-time').textContent = data.time;
      document.getElementById('appt-instructor').textContent = data.instructor;
      document.getElementById('appt-project').textContent = data.project;
      document.getElementById('appt-group').textContent = data.group;

      detailsDiv.querySelectorAll('p:not(#noAppointmentMsg)').forEach(p => p.classList.remove('d-none'));
      deleteBtn.classList.remove('d-none');
      noMsg.classList.add('d-none');

      scheduleBtn.classList.add('disabled');
      scheduleBtn.setAttribute('aria-disabled', 'true');

    } else {
      noMsg.classList.remove('d-none');
      deleteBtn.classList.add('d-none'); // Hide delete button if no appointment

      scheduleBtn.classList.remove('disabled');
      scheduleBtn.removeAttribute('aria-disabled');
    }
  }


  function fetchInstructorBookings(date) {
    console.log(`Simulating fetch bookings for date: ${date}`);
    // Wait for backend
    // TODO: Use fetch(`/api/instructor/bookings?date=${date}`)

    let bookingsData = [];
    if (date.endsWith('5')) {
      bookingsData = [
        { time: '09:00', students: 'Charlie, Dana', project: 'Web App Demo' },
        { time: '09:20', students: 'Eve', project: 'Database Design' },
        { time: '10:40', students: 'Frank, Grace', project: 'AI Model Report' }
      ];
    } else if (date.endsWith('6')) {
      bookingsData = [
        { time: '14:00', students: 'Heidi', project: 'Network Setup' }
      ];
    }
    // Should probably return an empty array if no bookings

    displayInstructorBookings(bookingsData);
  }

  function displayInstructorBookings(data) {
    if (!bookingsTableBody || !bookingsTable || !noBookingsMsg || !defaultMsg) return;

    bookingsTableBody.innerHTML = '';
    noBookingsMsg.classList.add('d-none');
    bookingsTable.classList.add('d-none');
    defaultMsg.classList.add('d-none');


    if (data && data.length > 0) {
      data.forEach(booking => {
        const row = bookingsTableBody.insertRow();
        row.innerHTML = `
                    <td>${booking.time}</td>
                    <td>${booking.students}</td>
                    <td>${booking.project}</td>
                    <td><button class="btn btn-sm btn-outline-secondary" disabled>Details</button></td> <!-- Action placeholder -->
                `;
      });
      bookingsTable.classList.remove('d-none'); // Show table
    } else {
      noBookingsMsg.classList.remove('d-none'); // Show "no bookings" message
    }
  }

  // Temp Login for storing demo role 
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      //  backend should login. This is just for debugging things
      e.preventDefault();

      const username = document.getElementById('username').value;
      const role = document.getElementById('role').value;

      if (username && role) {
        sessionStorage.setItem('userName', username);
        sessionStorage.setItem('userRole', role);

        if (role === 'student') {
          window.location.href = 'student_dashboard.html';
        } else if (role === 'instructor') {
          window.location.href = 'instructor_dashboard.html';
        } else {
          alert('invalid role selected.');
        }
      } else {
        alert('Fill in all fields.');
      }
    });
  }


}); // End DOMContentLoaded
