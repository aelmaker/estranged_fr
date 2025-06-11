#include "main.h"

//  https://github.com/AnastasiyaYatsenko/AEAT-9922

// тут може бути щось лишне
String gCode = "N/A";
String encoder = "N/A";
String voltagePD = "N/A";
String angle = "N/A";
String temperature = "N/A";
String stallguard = "N/A";
String error = "N/A";
String wsApi = "N/A";

extern void handleInterruptSW1_un_hold();
extern void handleInterruptSW2_backward();
extern void handleInterruptSW3_forward();

// Функція яка генерує HTML для кожної сторінки
String buildHTML(String page, AsyncWebServerRequest *rq)
{
  String html = F("<!DOCTYPE html><html lang='en'>");
  html += F("<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>");
  html += F("<title>QuStepper Pro</title>");
  html += F("<style>");

  // Основні стилі
  html += F("body { background-color: #3D3D3D; color: #FFFFFF; font-family: Arial, sans-serif; margin: 0; padding: 0; }");
  html += F("header { background-color: #D66B1F; color: #FFFFFF; padding: 10px 20px; text-align: center; }");
  html += F("header h1 { margin: 0; font-size: 24px; }");
  html += F("header p { text-align: center; margin: 5px 0; font-size: 16px; }");
  html += F("nav { margin: 20px 0; text-align: center; }");
  html += F("nav a { color: #D66B1F; text-decoration: none; margin: 0 10px; font-size: 18px; padding: 5px 10px; }");
  html += F("nav a:hover { text-decoration: underline; color: #A7BDA4; }");
  html += F("main { margin: 20px auto;  max-width: 500px; padding: 20px; text-align: center; justify-content: center;  align-items: center; }");
  html += F("footer { background-color: #2E2E2E; color: #CCCCCC; padding: 10px; text-align: center; font-size: 10px;  font-family: 'Courier New', Courier, monospace;}");

  html += F("table { border-collapse: collapse; width: 100%; font-family: sans-serif; } ");
  html += F(" th, td { border: 1px solid #ccc; padding: 8px; text-align: left; } ");
  html += F(" th { background-color: #f2f2f2; } ");

  html += F("form { margin: 20px auto; text-align: left; max-width: 500px; }");
  html += F("select, input, textarea { padding: 10px; font-size: 16px; margin: 5px 0; width: 100%; box-sizing: border-box; background-color: #D6D6D6; border: 1px solid #5D7264; }");

  html += F("ul {  list-style-type: none; padding: 0; margin: 0; text-align: left; }");
  html += F("li { text-align: left; display: inline-block; width: 100%; }");

  html += F("button { background-color: #D66B1F; color: #FFFFFF; padding: 10px 20px; font-size: 16px; border: none; cursor: pointer; margin: 5px 0; width: 100%; }");
  html += F("button:hover { background-color: #A7BDA4; color: #3D3D3D; }");

  html += F("#preslider { display: flex; align-items: center; justify-content: center; height: 30vh; }");
  html += F("#slider { position: relative; width: 200px; height: 200px; border: 5px solid #D66B1F; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle, #FFFFFF, #D6D6D6); }");

  html += F("#arrow { position: absolute; width: 5px; height: 80px; background-color:#5D7264; transform-origin: 50% 100%; transform: translateY(-40px) rotate(0deg); border-radius: 5px; }");
  html += F("#center { width: 20px; height: 20px; background-color:#D66B1F; border-radius: 50%; z-index: 1; }");
  html += F("#angle-display { margin-top: 20px; font-size: 1.5rem; color: #FFFFFF; }");

  html += F("h3 { color: #FFFFFF; background-color:#5D7264; font-size: 24px; font-weight: bold; text-align: center; margin: 20px 0; line-height: 1.5; text-transform: uppercase; }");
  html += F("p { text-align: center; margin: 5px 0; font-size: 12px; }");
  html += F("</style>");

  html += F("</head><body>");

  // Хедер

  html += F("<header style='background-color: #D66B1F; justify-content: center; padding: 10px; display: flex; align-items: center; gap: 10px;'>");
  html += F("<p><svg width='300' height='80' viewBox='0 0 600 150' xmlns='http://www.w3.org/2000/svg'>");

  // Stepper Motor Box
  html += F("<rect x='0' y='30' width='80' height='80' rx='10' fill='#D66B1F' stroke='#3D3D3D' stroke-width='6'/>");

  // Mounting Bolts
  html += F("<circle cx='10' cy='40' r='4' fill='#3D3D3D'/><circle cx='70' cy='40' r='4' fill='#3D3D3D'/>");
  html += F("<circle cx='10' cy='100' r='4' fill='#3D3D3D'/><circle cx='70' cy='100' r='4' fill='#3D3D3D'/>");

  // Center Rings
  html += F("<circle cx='40' cy='70' r='22' fill='#FFFFFF' stroke='#3D3D3D' stroke-width='6'/>");
  html += F("<circle cx='40' cy='70' r='10' fill='#D66B1F'/>");

  // U-shaped cable (stylized)
  html += F("<path d='M40 92 v20 a10 10 0 0 0 10 10 h10' stroke='#5D7264' stroke-width='8' fill='none'/>");
  html += F("<path d='M40 92 v20 a10 10 0 0 1 -10 10 h-10' stroke='#A7BDA4' stroke-width='8' fill='none'/>");

  // Conveyor Belt
  html += F("<rect x='80' y='65' width='400' height='10' rx='5' fill='#D6D6D6' stroke='#3D3D3D' stroke-width='5'/>");

  // Conveyor Rollers
  for (int x = 100; x <= 460; x += 40)
  {
    html += F("<circle cx='");
    html += String(x);
    html += F("' cy='70' r='5' fill='#3D3D3D'/>");
  }

  // Conveyor End Caps
  html += F("<circle cx='85' cy='70' r='8' fill='#3D3D3D'/><circle cx='480' cy='70' r='8' fill='#3D3D3D'/>");

  // Text: QUStepper
  html += F("<text x='95' y='45' font-size='30' font-weight='bold' font-family='Arial, sans-serif'>");
  html += F("<tspan fill='#3D3D3D'>QU</tspan>");
  html += F("<tspan fill='#FFFFFF'>Stepper</tspan>");
  html += F("</text>");

  html += F("</svg></p>");
  html += F("<p>Advanced Control <br> for your stepper motors</p>");
  html += F("</header>");

  GlobalStateUpdate(); // Оновлюємо глобальний стан
  // якщо глобально в Preferences відсутня нава драйверу 2209 2103 і тд то виводиться стартове налаштування (як на відео)
  if (Global_prefDriverName != 0)
  { 
    // Навігація
    html += F("<nav>");
    html += F("<a href='/control'>Control</a>");
    html += F("<a href='/status'>Status</a>");
    html += F("<a href='/gprograming'>G-Code</a>");
    html += F("<a href='/logs'>Logs</a>");
    html += F("<a href='/configuration'>Configuration</a>");
    html += F("<a href='/apiinfo'>API-Info</a>");
    html += F("</nav>");
  }
  else
  {
    html += "<h3>Welcome to QuStepper Pro (First start)</h3>";
    html += "<p>Select an option from the menu to get started.</p>";
    html += F("<form action='/firststart' method='POST'>");
    html += F("Set Driver: <select name='driver'>");

    html += F("<option value='2240'>TMC2240 (SPI)</option>");
    html += F("<option value='2130'>TMC2130 (SPI)</option>");
    html += F("<option value='2209'>TMC2209 (UART)</option>");
    html += F("<option value='2226'>TMC2226 (UART)</option>");
    html += F("<option value='4988'>A4988</option>");
    html += F("<option value='8824'>DRV8824</option>");
    html += F("</select><br>");

    html += F("<button type='submit'>Start</button>");
    html += F("<button onclick='location.reload()'>Reload page</button>");

    html += F("</form>");

    drawText(1, 10, 10, "Start driver config.");
  }

  // Основний контент
  html += "<main>";

  if (page == "apicontrol")
  {
  }
  else if (page == "control")
  {
    angle = String(getPosition(false));
    html += F("<h2>Control</h2>");
    // ебейший скрипт на який боюся дихати)
    html += "<p><div id='preslider'><div id='slider'> <div id='arrow'></div> <div id='center'></div> </div> </div> <p id='angle-display'>Angle: " + angle + "°</p>";
    html += F(R"rawliteral(

 <script> const slider = document.getElementById('slider');
    const arrow = document.getElementById('arrow');
    const angleDisplay = document.getElementById('angle-display');
    let angle = 0;

    )rawliteral");

    html += "arrow.style.transform = `translateY(-40px) rotate(${" + angle + "}deg)`;";

    html += F(R"rawliteral( 
    const ws = new WebSocket('ws://' + location.host + '/ws');

 ws.onopen = () => {console.log('WebSocket connected');  };
 console.log('Setting WebSocket onmessage');
 ws.onmessage = (event) => {
    console.log('From server:', event.data);
    document.getElementById('wsAngle').innerText = event.data;  
    angle=event.data;                                           
 };

 ws.onerror = (error) => {console.error('WebSocket error:', error);  };
 ws.onclose = () => {console.warn('WebSocket connection closed');  };
 
 slider.addEventListener('mousedown', (e) => { 
 const rect = slider.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;
    const x = event.clientX - centerX;
    const y = event.clientY - centerY;
    angle = Math.round((Math.atan2(y, x) * 180) / Math.PI + 360) % 360;
    angle = (angle + 90) % 360;
    arrow.style.transform = `translateY(-40px) rotate(${angle}deg)`;
    angleDisplay.textContent = `Angle: ${angle}°`;

const moveHandler = (event) => { 
    
    const x = event.clientX - centerX;
    const y = event.clientY - centerY;
    angle = Math.round((Math.atan2(y, x) * 180) / Math.PI + 360) % 360;
    angle = (angle + 90) % 360;
    arrow.style.transform = `translateY(-40px) rotate(${angle}deg)`;
    angleDisplay.textContent = `Angle: ${angle}°`;  

};

 const upHandler = () => {
    slider.removeEventListener('mousemove', moveHandler);   
    slider.removeEventListener('mouseup', upHandler);   
    
    if (ws.readyState === WebSocket.OPEN) {ws.send(angle);
    } else {console.warn('WebSocket is not open');
    } };
    slider.addEventListener('mousemove', moveHandler);  
    slider.addEventListener('mouseup', upHandler);  
 
 }); </script>

 )rawliteral");

    html += "<p id='wsAngle'>Encoder angle:" + angle + "</p>";

    //  ws.textAll(angle);
    html += F("<form action='/control' method='POST'>");

    html += "<p>Microstepping:" + getMicrostepDescription(Global_prefDriverStep) + "</p>";
    html += "<p>Speed: " + String(Global_prefSpeedMotor) + " steps/s</p> ";
    html += "<p>Acceleration: " + String(Global_prefAccelstep) + " steps/s²</p>";

    html += F("Input Steps: <input type='number' name='steps'><br>");

    html += F("<button type='submit' name='checkpost' value='forward'>Run Forward</button>");
    html += F("<button type='submit' name='checkpost' value='backward'>Run Backward</button>");
    html += F("<button type='submit' name='checkpost' value='hold'>Hold</button>");
    html += F("<button type='submit' name='checkpost' value='unhold'>UnHold</button>");
    html += F("</form>");
  }
  else if (page == "gprograming")
  {
    html += F("<h2>G-Programing</h2>");
    html += F("<table>"
              "<tr><th>Command</th><th>Description</th></tr>"
              "<tr><td><code>G1 X10</code></td><td>Move <b>forward by 10 steps</b></td></tr>"
              "<tr><td><code>G1 X-20</code></td><td>Move <b>backward by 20 steps</b></td></tr>"
              "<tr><td><code>M17</code></td><td>Enable motor holding torque (Hold / Enable)</td></tr>"
              "<tr><td><code>M18</code></td><td>Disable motor holding torque (Unhold / Disable)</td></tr>"
              "<tr><td><code>G4 P1000</code></td><td>Delay for <b>1000 milliseconds</b></td></tr>"
              "<tr><td><code>M114</code></td><td>Output current motor position</td></tr>"
              "</table>");
    html += F("<form action='/gprograming' method='POST'>");
    html += F("<textarea name='gcode' rows='6'></textarea><br>");
    html += F("<button type='submit'>Start</button>");
    html += F("<button type='submit' formaction='/loop'>Start While</button>");
    html += F("</form>");
  }
  else if (page == "logs")
  {
    html += F("<h2>Logs</h2>");
    html += "<div style='white-space: pre-wrap; text-align: left; margin: 0 auto; max-width: 500px;'>" + generateLogsHTML() + "</div>";
  }
  else if (page == "apiinfo")
  {

    html += F("<h2>API Documentation</h2>");
    // CE HUINYA TAK ARTEM NE TREBA!
    pref.begin(PREF_NAMESPACE);
    uint32_t apipass = pref.getUInt(PREF_APIPASS, 0);
    pref.end();

    if (apipass != 0)
    {
      html += "<p>API password: Installed </p>";
    }
    else
    {
      html += "<p>API password: <b>Not set</b></p>";
    }

    // TODO
    html += F("<p>This API allows you to control the stepper motor via HTTP POST requests. Below is the list of available commands and their usage:</p>");

    html += F("<h3>1. Authentication</h3>");
    html += F("<p><b>Endpoint:</b> /apicontrol?command=apilogin&pass=&lt;password&gt;</p>");
    html += F("<p><b>Parameters:</b></p>");
    html += F("<ul><li><b>command:</b> apilogin</li><li><b>pass:</b> Password set in the configuration</li></ul>");
    html += F("<p><b>Response:</b></p>");
    html += F("<ul><li>Success: {\"apitoken\": \"&lt;generated_token&gt;\"}</li><li>Failure: {\"apitoken\": \"NULL\"}</li></ul>");

    html += F("<h3>2. Get Status</h3>");
    html += F("<p><b>Endpoint:</b> /apicontrol?command=status&apitoken=&lt;apitoken&gt;</p>");
    html += F("<p><b>Parameters:</b></p>");
    html += F("<ul><li><b>command:</b> status</li><li><b>apitoken:</b> Token obtained after authentication</li></ul>");
    html += F("<p><b>Response:</b> {\"status\": \"&lt;current_state&gt;\"}</p>");

    html += F("<h3>3. Get All States</h3>");
    html += F("<p><b>Endpoint:</b> /apicontrol?command=qustate&apitoken=&lt;apitoken&gt;</p>");
    html += F("<p><b>Parameters:</b></p>");
    html += F("<ul><li><b>command:</b> qustate</li><li><b>apitoken:</b> Token obtained after authentication</li></ul>");
    html += F("<p><b>Response:</b> JSON object with full system state information</p>");

    html += F("<h3>4. Hold Motor</h3>");
    html += F("<p><b>Endpoint:</b> /apicontrol?command=hold&apitoken=&lt;apitoken&gt;</p>");
    html += F("<p><b>Parameters:</b></p>");
    html += F("<ul><li><b>command:</b> hold</li><li><b>apitoken:</b> Token obtained after authentication</li></ul>");
    html += F("<p><b>Response:</b> {\"status\": \"success\"}</p>");

    html += F("<h3>5. Unhold Motor</h3>");
    html += F("<p><b>Endpoint:</b> /apicontrol?command=unhold&apitoken=&lt;apitoken&gt;</p>");
    html += F("<p><b>Parameters:</b></p>");
    html += F("<ul><li><b>command:</b> unhold</li><li><b>apitoken:</b> Token obtained after authentication</li></ul>");
    html += F("<p><b>Response:</b> {\"status\": \"success\"}</p>");

    html += F("<h3>6. Move Motor Forward</h3>");
    html += F("<p><b>Endpoint:</b> /apicontrol?command=forward&steps=&lt;steps&gt;&apitoken=&lt;apitoken&gt;</p>");
    html += F("<p><b>Parameters:</b></p>");
    html += F("<ul><li><b>command:</b> forward</li><li><b>steps:</b> Number of steps to move forward</li><li><b>apitoken:</b> Token obtained after authentication</li></ul>");
    html += F("<p><b>Response:</b> {\"status\": \"success\"}</p>");

    html += F("<h3>7. Move Motor Backward</h3>");
    html += F("<p><b>Endpoint:</b> /apicontrol?command=backward&steps=&lt;steps&gt;&apitoken=&lt;apitoken&gt;</p>");
    html += F("<p><b>Parameters:</b></p>");
    html += F("<ul><li><b>command:</b> backward</li><li><b>steps:</b> Number of steps to move backward</li><li><b>apitoken:</b> Token obtained after authentication</li></ul>");
    html += F("<p><b>Response:</b> {\"status\": \"success\"}</p>");

    html += F("<h3>8. Set Zero Position</h3>");
    html += F("<p><b>Endpoint:</b> /apicontrol?command=setzero&apitoken=&lt;apitoken&gt;</p>");
    html += F("<p><b>Parameters:</b></p>");
    html += F("<ul><li><b>command:</b> setzero</li><li><b>apitoken:</b> Token obtained after authentication</li></ul>");
    html += F("<p><b>Response:</b> {\"status\": \"success\"}</p>");

    html += F("<h3>9. Move Motor to Angle</h3>");
    html += F("<p><b>Endpoint:</b> /apicontrol?command=angle&angle=&lt;angle&gt;&apitoken=&lt;apitoken&gt;</p>");
    html += F("<p><b>Parameters:</b></p>");
    html += F("<ul><li><b>command:</b> angle</li><li><b>angle:</b> Target angle in degrees</li><li><b>apitoken:</b> Token obtained after authentication</li></ul>");
    html += F("<p><b>Response:</b> {\"status\": \"success\"}</p>");

    html += F("<h3>10. Get Encoder Position</h3>");
    html += F("<p><b>Endpoint:</b> /apicontrol?command=getenc&apitoken=&lt;apitoken&gt;</p>");
    html += F("<p><b>Parameters:</b></p>");
    html += F("<ul><li><b>command:</b> getenc</li><li><b>apitoken:</b> Token obtained after authentication</li></ul>");
    html += F("<p><b>Response:</b> {\"encoderPos\": \"&lt;position&gt;\"}</p>");

    html += F("<h3>Error Responses</h3>");
    html += F("<ul><li><b>401 Unauthorized:</b> Invalid token</li><li><b>400 Bad Request:</b> Incorrect request or missing parameters</li><li><b>200 OK:</b> Successful request</li></ul>");
  }
  else if (page == "resetwifi")
  {

    wifiManager.resetSettings();
    html += F("<h3>Reset Wi-fi</h3>");
    html += F("<p>The settings have been reset, the restart will follow</p>");
    delay(10000);
    ESP.restart();

    // тут хуйня не вивелась
  }
  else if (page == "configuration")
  {

    GlobalStateUpdate();  

    html += F("<h2>Configuration</h2>");
    html += F("<h3>Microstepping</h3><form action='/configuration' method='POST'>");
    // html += "<p>Сurrent Value:" + String(Global_prefDriverStep) + "</p>";
    html += "<p>Сurrent Value:" + String(Global_prefDriverStep) + "</p>";
    html += F("Microstepping: <select name='microstep'>");

    for (int i = 1; i <= 6; i++)
    {
      html += "<option value='" + String(i) + "'>" + getMicrostepDescription(i) + "</option>";
    }

      html += F("</select><br>");
      html += F("<button type='submit' name='setstep_button' value='1'>Set microstep</button>");
      html += F("</form>");

      html += F("<h3>Speed motor</h3><form action='/configuration' method='POST'>");
      html += "<p>Сurrent Value: " + String(Global_prefSpeedMotor) + "</p>";
      html += F("Input speed: <input type='number' name='setspeed'><br>");
      html += F("<p  style='font-size: 0.7  em; color: gray;'>10-1000 steps/s</p>");
      html += F("<button type='submit' name='speed_button' value='1'>Set speed</button>");
      html += F("</form>");

      html += F("<h3>Acceleration</h3><form action='/configuration' method='POST'>");
      html += "<p>Сurrent Value: " + String(Global_prefAccelstep) + "</p>";
      html += F("Input acceleration: <input type='number' name='setaccel'><br>");
      html += F("<p  style='font-size: 0.7  em; color: gray;'>100-1000 steps/s²: For smooth starting and stopping without loss of steps under light load.<br>1000-5000 steps/s²: For faster systems with medium loads.<br>5000+ steps/s²: Can be used if the system is configured for high-speed operation with minimal inertial load (light rotor, optimal shaft weight).</p>");
      html += F("<button type='submit' name='accel_button' value='1'>Set acceleration</button>");
      html += F("</form>");

      html += F("<h3>API Password</h3><form action='/configuration' method='POST'>");

      pref.begin(PREF_NAMESPACE);
      uint32_t apipass = pref.getUInt(PREF_APIPASS, 0);
      pref.end();

      if (apipass != 0)
      {
        html += "<p>API password: Installed </p>";
      }
      else
      {
        html += "<p>API password: <b>Not set</b></p>";
      }

      html += F("Input password: <input type='text' name='setapipassword'><br>");
      html += F("<button type='submit' name='api_button' value='1'>Set API password</button>");
      html += F("</form>");

      html += F("<hr/><form action='/configuration' method='POST' style='margin-top:10px;'>");
      html += F("<input type = 'hidden' name = 'setzero' value = '1'>");
      html += F("<button type='submit' style='background-color:yellow; color:black;'>Set Zero (encoder) </button>");
      html += F("</form>");

      html += F("<form action='/resetconfig' method='POST' style='margin-top:10px;'>");
      html += F("<input type = 'hidden' name = 'rstconf' value = '1'>");
      html += F("<button type='submit' style='background-color:red; color:white;'>Reset Configuration</button>");
      html += F("</form>");

      html += F("<form action='/resetwifi' method='POST' style='margin-top:10px;'>");
      html += F("<button type='submit' style='background-color:red; color:white;'>Reset Wifi</button>");
      html += F("</form>");
    }
    else if (page == "resetconfig")
    {
      if (rq->hasParam("rstconf", true))
      {
        String truerst = rq->getParam("rstconf", true)->value();
        // rstconf  = 1
        if (truerst == "1")
        {
          pref.begin(PREF_NAMESPACE, false);

          if (pref.clear())
          {
            html += "<h3>The reset was successful</h3>";
            html += "<p>Driver was reseted</p>";
            drawText(1, 5, 5, "Driver was reseted");
          }
          else
          {
            html += "<h3>Reset failed</h3>";
            html += "<p>Restart the device</p>";
          }
          pref.end();
        }
      }
      else
      {
        html += "<h3>Anti Web-crawler</h3>";
        html += "<p>Suspicious activity has been detected</p>";
      }
    }
    else if (page == "firststart")
    {

      drawText(0, 5, 28, "Insert driver..");
      if (rq->hasParam("driver", true))
      {
        String driverName = rq->getParam("driver", true)->value();

        pref.begin(PREF_NAMESPACE);
        pref.putInt(PREF_NAMEDRIVER, driverName.toInt()); // Write set driver
        pref.end();

        driver.set_driverType(driverName.toInt());

        // SPI drivers
        if (driverName == "5160" || driverName == "2130" || driverName == "2240")
        {
        }
        else // UART drivers
        {
        }

        html += "<p>Your selected driver: " + driverName + "</p>";

        html += F("<p>1. Turn off the device</p>");
        html += F("<p>2. Insert the driver</p>");
        html += F("<p>3.Turn on the device</p>");

        String ipadr = WiFi.localIP().toString() + ":" + WebViewPort;
        html += "<p>4. Click to http://" + ipadr + "</p>";
      }
      else
      {
        html += "<p>No POST[] param driver</p>";
      }
    }
    else
    {
      encoder = String(getPosition(true));
      angle = String(getPosition(false));
      voltagePD = String(readVoltagePD());

      uint16_t sg = driver.get_stallguard();
      String sg_str = "N/A";
      // Serial.printf("SG: %d" ,sg);
      if ((sg != NULL) && (currentStep < targetSteps))
      {
        sg_str = String(sg);
      }
      String temp_str = driver.get_str_temperature();

      html += F("<h2>Status module</h2>");
      html += "Encoder: " + encoder + "<br>";
      html += "Angle: " + angle + "<br>";
      html += "Temperature: " + temp_str + "<br>";
      html += "Stallguard: " + sg_str + "<br>";
      html += "Error: " + error + "<br>";
      html += "Voltage: " + voltagePD + "<br>";
    }

    html += F("</main>");

    // Футер
    html += "<footer>";
    html += "<p>&copy; 2024 QuStepper Pro. All rights reserved.</p>";
    html += GlobalStateInfo(false);
    html += "</footer>";

    html += "</body></html>";
    return html;
  }

  void setZeroSoftware()
  {
    double data = 0;
    aeat.init_pin_ssi();
    data = aeat.ssi_read_pins(17); // read 17 bits
    Global_prefAngZero = double(data) * 360.0 / 262144.0;
    pref.begin(PREF_NAMESPACE);
    pref.putDouble(PREF_ZEROPOS, Global_prefAngZero);
    pref.end();
  }

  double getPosition_old(bool how)
  {
    double data = 0;
    // aeat.setup_ssi3();
    // data = aeat.ssi_read();
    // Serial.printf("SSI HW   0x%04lx=%6lld=%7.3Lf \n",
    //               data, data, double(data) * 360.0 / 262144.0);
    aeat.init_pin_ssi();
    data = aeat.ssi_read_pins(17); // read 17 bits
    // Serial.printf("SSI PINS 0x%04lx=%6lld=%7.3Lf \n",
    //               data, data, double(data) * 360.0 / 262144.0);
    // data = aeat.spi_read(0x3f);
    // Serial.printf("SPI HW   0x%04lx=%6lld=%7.3Lf \n",
    //               data, data, double(data) * 360.0 / 262144.0);
    // Serial.print("------------\n");

    if (how)
    {
      return data;
    }
    else
    {
      return double(data) * 360.0 / 262144.0;
    }
  }

  double getPosition(bool how)
  {
    double data = 0;
    aeat.init_pin_ssi();
    data = aeat.ssi_read_pins(17); // read 17 bits

    if (how)
    {
      return data; // TODO take zero into account
    }
    else
    {
      return shiftZeroAng(double(data) * 360.0 / 262144.0);
    }
  }

  float shiftZeroAng(float ang_actual)
  {
    float ang = ang_actual - Global_prefAngZero;
    if (ang < 0.0)
      ang = 360.0 + ang;
    return ang;
  }

  int moveMotorAng(double angle)
  {
    double lastPosAngle = getPosition(false);

    double pos_ang = abs(lastPosAngle - angle);
    // double inverse_pos_ang = abs(360.0 - pos_ang);
    // double actualPosAngle;
    bool dir = true;

    /* виставили в яку сторону ехать мотору*/
    if (lastPosAngle < angle)
    {
      dir = true;
    }
    else
    {
      dir = false;
    }

    // set microstepping
    //  uint32_t anglePsteps = (actualPosAngle * (8 * motorStep * Global_prefDriverStep)) / 360; //angle to steps
    //  Serial.printf("Global ms: %d\n", Global_prefDriverStep);
    uint32_t anglePsteps = (200 * pos_ang * Global_prefDriverStep) / 360; // angle to steps

    // Serial.printf("Prev: %f, new: %f\nPos ang: %f, actual: %f\ndir: %s, steps: %d\n", lastPosAngle, angle, pos_ang, pos_ang, dir ? "forward" : "backward", anglePsteps);

    return moveMotor(anglePsteps, dir, Global_prefAccelstep);
  }

  void setup()
  {

    WiFi.mode(WIFI_STA);
    // Serial.begin(115200);
    if (Wire.begin(SDA_PIN, SCL_PIN))
    {
      addLog("I2C - Start");
    }
    else
    {
      addLog("I2C - Error");
    }

    if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C))
    { // 0x3C - стандартна I2C адреса
      ESP_LOGD("Display", "Error init display");
      addLog("Display - Error");
    }
    else
    {
      addLog("Display - Start");
      testdrawbitmap();
      setLed2Effect(LED1, LED2, 1);
    }

    display.clearDisplay();
    display.setTextColor(SSD1306_WHITE);
    display.setTextSize(1);

    driver.set_serial(serial_stream, RX_TMC, TX_TMC);
    // driver.set_driverType(2209); // TODO

    driver.get_temperature();

    pinMode(LED1, OUTPUT);
    pinMode(LED2, OUTPUT);
    pinMode(VBUS_SENSE, INPUT); // Пін для вимірювання напруги

    pinMode(AEAT_MSEL, OUTPUT);
    digitalWrite(AEAT_MSEL, HIGH);
    SPI.begin(V_SCK, V_MISO, V_MOSI, AEAT_CS);

    // Далі нлаштування кнопок
    pinMode(SW1, INPUT_PULLUP);
    pinMode(SW2, INPUT_PULLUP);
    pinMode(SW3, INPUT_PULLUP);

    pinMode(CFG1_PIN, OUTPUT);
    pinMode(CFG2_PIN, OUTPUT);
    pinMode(CFG3_PIN, OUTPUT);

    attachInterrupt(digitalPinToInterrupt(SW1), handleInterruptSW1_un_hold, FALLING); // falling?
    attachInterrupt(digitalPinToInterrupt(SW2), handleInterruptSW2_backward, CHANGE);
    attachInterrupt(digitalPinToInterrupt(SW3), handleInterruptSW3_forward, CHANGE);
    setupMotor();

    if (pref.begin(PREF_NAMESPACE, true))
    {
      addLog("Preferences - Work Good");
      drawText(1, 5, 5, "Preferences - Work Good");
    }
    else
    {
      addLog("Preferences - Error");
      drawText(1, 5, 5, "Preferences - Error");
    }

    Global_prefDriverName = pref.getInt(PREF_NAMEDRIVER, 0);
    uint32_t ms = 8;
    if (Global_prefDriverName != 0)
    {   
      // тут треба відремонтувати на SHORT
      driver.set_driverType(Global_prefDriverName);
      ms = pref.getInt(PREF_STEPCOUNT, 8);
      Global_prefAccelstep = pref.getInt(PREF_ACCELSTEP, 800);
      Global_prefSpeedMotor = pref.getInt(PREF_SPEEDMOTOR, 500);
      Global_prefAngZero = pref.getDouble(PREF_ZEROPOS, 0.0);
    }

    pref.end();


    Global_prefDriverStep = pow(2, ms);
    driver.set_microstep(ms);

    String Point = WiFi.macAddress();
    Point.replace(":", ""); // Видаляємо двокрапки з MAC-адреси
    Point = "QuStepper-" + Point.substring(Point.length() - 4);

    drawText(1, 0, 0, Point.c_str());

    drawText(0, 0, 15, "Password: qustepper");
    voltagePD = "In. Voltage: " + String(readVoltagePD());
    drawText(0, 90, 0, voltagePD.c_str());

    // Тут піднімається точка доступу якщо немає поряд прописанного Wifi на 300 сек до першого підключення
    wifiManager.setConfigPortalTimeout(300);

    if (!wifiManager.autoConnect(Point.c_str(), "qustepper")) //"QuStepper", "qstep"
    {
      drawText(1, 0, 0, "Failed WiFi");
      ESP_LOGD("Wi-Fi", "Failed to connect to WiFi.");
    }
    else
    {
      drawText(1, 0, 0, "WiFi connected!");
      setLed2Effect(LED1, LED2, 2);
      String ipadr = WiFi.localIP().toString() + ":" + WebViewPort;
      drawText(0, 0, 20, ipadr.c_str());
      ESP_LOGD("IP Address", "%s", ipadr);


      for(int i = 0; i < 15; i++)
      {
        drawText(1, 5, 5, "-+++++");  
      }

      server.on("/", HTTP_GET, [](AsyncWebServerRequest *request)
                {
              
                request->send(200, "text/html", buildHTML("default", request)); // Головна сторінка
                addLog("Page - /"); 
                 drawText(1, 5, 5, "Main menu"); });

      server.on("/control", HTTP_GET, [](AsyncWebServerRequest *request)
                {
                request->send(200, "text/html", buildHTML("control", request)); // Сторінка Control
                addLog("Page - /control");
                drawText(1, 5, 5, "Page - /control"); });

      server.on("/status", HTTP_GET, [](AsyncWebServerRequest *request)
                {
                request->send(200, "text/html", buildHTML("status", request)); // Сторінка Status
                addLog("Page - /status"); 
                 drawText(1, 5, 5, "Page - /status"); });

      server.on("/gprograming", HTTP_GET, [](AsyncWebServerRequest *request)
                {
                request->send(200, "text/html", buildHTML("gprograming", request)); // Сторінка G-Programing
                addLog("Page - /progrraming");
                drawText(1, 5, 5, "Page - /progrraming"); });

      server.on("/logs", HTTP_GET, [](AsyncWebServerRequest *request)
                {
                addLog("Page - /logs"); 
                drawText(1, 5, 5, "Page - /logs");

                logGlobalState(); 
                request->send(200, "text/html", buildHTML("logs", request)); });

      server.on("/apiinfo", HTTP_GET, [](AsyncWebServerRequest *request)
                {
                   addLog("Page - /apiinfo");   
                   drawText(1, 5, 5, "Page - /apiinfo");   
   
                   logGlobalState(); 
                   request->send(200, "text/html", buildHTML("apiinfo", request)); });

      server.on("/resetwifi", HTTP_POST, [](AsyncWebServerRequest *request)
                {
                request->send(200, "text/html", buildHTML("resetwifi", request)); 
                addLog("Page - /resetwifi"); });

      server.on("/configuration", HTTP_GET, [](AsyncWebServerRequest *request)
                {
                request->send(200, "text/html", buildHTML("configuration", request)); 
                addLog("Page - /configuration"); 
                drawText(1, 5, 5, "Page - /configuration"); });

      server.on("/configuration", HTTP_POST, [](AsyncWebServerRequest *request)
                {

                  

        String getParamRequest="";
      if (request->hasParam("microstep", true)) {
        getParamRequest = request->getParam("microstep", true)->value();
        addLog("Save microstep");
        uint32_t ms = getParamRequest.toInt();
        pref.begin(PREF_NAMESPACE);
        pref.putInt(PREF_STEPCOUNT, ms);
        pref.end();
        // stepper_driver.setMicrostepsPerStepPowerOfTwo(microstep.toInt());
        driver.set_microstep(ms);
        Global_prefDriverStep = pow(2, ms);
        // TODO set microsteps to driver

    } else if ( request->hasParam("setaccel", true)) {
        getParamRequest = request->getParam("setaccel", true)->value();
        pref.begin(PREF_NAMESPACE);
        pref.putInt(PREF_ACCELSTEP, getParamRequest.toInt());   
        pref.end();

        ESP_LOGD("Acceleration", "Set accell: %s", getParamRequest.c_str());
       addLog("Set accell");
       // TODO set accel to driver
    }

    else if ( request->hasParam("setaccel", true)) {
      getParamRequest = request->getParam("setaccel", true)->value();
      pref.begin(PREF_NAMESPACE);
      pref.putInt(PREF_ACCELSTEP, getParamRequest.toInt());   
      pref.end(); 

      ESP_LOGD("Acceleration", "Set accell: %s", getParamRequest.c_str());
     addLog("Set accell");
  }

    else if (request->hasParam("setzero", true)) {  
        String getParamRequest = request->getParam("setzero", true)->value();   
        // pref.begin(PREF_NAMESPACE);   
        // pref.putInt(PREF_ZERO, getParamRequest.toInt());   
        // pref.end();     

        GlobalStateNow = STATE_IDLE;
        ws.textAll(String(getPosition(false)));

        // aeat.reset_zero();
        // aeat.set_zero();  
        setZeroSoftware();

        ESP_LOGD("Zero", "Set zero: %s", getParamRequest.c_str());
       addLog("Set zero position"); 
    }

    else if (request->hasParam("setspeed", true)) {  
        getParamRequest = request->getParam("setspeed", true)->value();   
        pref.begin(PREF_NAMESPACE);   
        pref.putInt(PREF_SPEEDMOTOR, getParamRequest.toInt());   
        pref.end();     
        ESP_LOGD("Speed motor", "Set speed: %s", getParamRequest.c_str());
       addLog("Set speed"); 
    }

    else if (request->hasParam("setapipassword", true)) {  
      String getParamRequest = request->getParam("setapipassword", true)->value();   

      pref.begin(PREF_NAMESPACE);     
      pref.putUInt(PREF_APIPASS, stringToCRC32(getParamRequest));   
      pref.end();     
      ESP_LOGD("API pass", "Set API pass: %s", getParamRequest.c_str());
      ESP_LOGD("API pass", "Set API pass: %d", stringToCRC32(getParamRequest)); 
     addLog("Set API pass"); 
  }

    request->redirect("/configuration"); });

      server.on("/resetconfig", HTTP_POST, [](AsyncWebServerRequest *request)
                {
                request->send(200, "text/html", buildHTML("resetconfig", request)); 
                addLog("Page - /resetconfig"); });

      server.on("/firststart", HTTP_POST, [](AsyncWebServerRequest *request)
                {
                request->send(200, "text/html", buildHTML("firststart", request)); 
                addLog("Page - /firststart"); });

      server.onNotFound([](AsyncWebServerRequest *request)
                        { request->send(404, "text/plain", "Page not found");
                      addLog("Page - /404");
                      drawText(1, 5, 5, "Page - /404"); });

      server.on("/apicontrol", HTTP_POST, [](AsyncWebServerRequest *request)
                {
      // Спочатку ПЕРЕВІРКА потім GETPARAM інакше буде NULLPTR
      if (request->hasParam("command", true))
      {
        String GetParamCommand = request->getParam("command", true)->value();
        if (GetParamCommand == "apilogin") // apicontrol?command=apilogin&pass=12345678
        {
          if (request->hasParam("pass", true))
          {
            pref.begin(PREF_NAMESPACE);
            uint32_t passCloser = pref.getUInt(PREF_APIPASS, 0); // Отримали пароль який задано в адмінці
            ESP_LOGD("API pass", "GET API pass: %u", passCloser);
            pref.end();

            String JsonSend = "";
            if (passCloser != 0)
            { // закодували та порівняли із тим що у запиті прийшов
              // if (stringToCRC32(request->getParam("pass", true)->value()) == stringToCRC32(String(passCloser)))
              if (stringToCRC32(request->getParam("pass", true)->value()) == passCloser)
              {
                Global_ApiToken = generateApiToken(); // згенерували апі токен для керування
                JsonSend = "{\"apitoken\": \"" + Global_ApiToken + "\"}";
                addLog("API - pass OK");
                ESP_LOGD("Global_ApiToken", "GET API token: %s", Global_ApiToken.c_str());
                drawText(1, 0, 0, "[API] - Gen Token");
              }
              else
              {
                JsonSend = "{\"apitoken\": \"NULL\"}";
                ESP_LOGD("API pass", "Password not valid");
                addLog("API - NULL pass");
                drawText(1, 0, 0, "[API] - NonValid pass");
              }
              request->send(200, "text/html", JsonSend);
            }
            else
            {
              request->send(400, "text/html", "{\"status\": \"Invalid password\"}");
            }
          }
        }
        else if (request->hasParam("apitoken", true))
        {
          if (request->getParam("apitoken", true)->value() == Global_ApiToken)
          {
           
            if (GetParamCommand == "status")
            {
              String statusNow = "{\"status\": \"" + String(GlobalStateNow) + "\"}";
              addLog("Status - ?????");
              drawText(1, 0, 0, "[API]-Get Status");
              ESP_LOGD("Status", "GET API status: %s", statusNow.c_str());
              logGlobalState();     
              request->send(200, "text/html", statusNow);
              return;
            } 
            else if (GetParamCommand == "qustate")
            {
              addLog("Status - ?????");
              drawText(1, 0, 0, "[API]-Get All State");
              request->send(200, "text/html", GlobalStateInfo(true));
              return;
            } 
            else  if (GetParamCommand == "hold")
              {
                digitalWrite(TMC_EN, LOW);
                GlobalStateNow = STATE_API_HOLD;
                ESP_LOGD("Motor - Hold", "GET API hold: %s", GetParamCommand.c_str());
                addLog("Motor - Hold");
                drawText(1, 0, 0, "[API]->Hold");
                request->send(200, "text/html", "{\"status\": \"success\"}");
                return;
              }
            else if (GetParamCommand == "unhold")
              {
                digitalWrite(TMC_EN, HIGH);
                GlobalStateNow = STATE_API_UNHOLD;
                ESP_LOGD("Motor - Unhold", "GET API unhold: %s", GetParamCommand.c_str());
                addLog("Motor - Unhold");
                drawText(1, 0, 0, "[API]->Unhold");
                request->send(200, "text/html", "{\"status\": \"success\"}");
                return;
              }

            if (request->hasParam("steps", true)) // apicontrol?apitoken=1234567&steps=3000&
            {
              String getParam = request->getParam("steps", true)->value(); 
              uint32_t stepsCount = getParam.toInt();
              ESP_LOGD("API Control", "Received steps: %d", stepsCount);
              if (GetParamCommand == "forward" && (GlobalStateNow == STATE_IDLE || GlobalStateNow == STATE_API_HOLD || GlobalStateNow == STATE_API_UNHOLD))
              {
                moveMotor(stepsCount, true, Global_prefAccelstep);
                GlobalStateNow = STATE_API_FORWARD;
                drawText(1, 0, 0, "[API]-MOTOR->FORWARD");
                
                addLog("API -> MOTOR -> Forward");

                request->send(200, "text/html", "{\"status\": \"success\"}");
               return;
              }
              else if (GetParamCommand == "backward" && (GlobalStateNow == STATE_IDLE || GlobalStateNow == STATE_API_HOLD || GlobalStateNow == STATE_API_UNHOLD))
              {
                moveMotor(stepsCount, false, Global_prefAccelstep);
                GlobalStateNow = STATE_API_BACKWARD;
                drawText(1, 0, 0, "[API]-MOTOR->Backward"); 

                addLog("Motor - Backward");
                request->send(200, "text/html", "{\"status\": \"success\"}");
                return;
              }
              else
              {
                request->send(400, "text/html", "{\"status\": \"busy\"}");
                addLog("API - motor busy");
                drawText(1, 0, 0, "[API]-MOTOR->Busy");
              }
            }
            else if (GetParamCommand == "setzero")
            {
              // String getParamRequest = request->getParam("setzero", true)->value();  
              GlobalStateNow = STATE_IDLE;
              ws.textAll(String(getPosition(false)));

              setZeroSoftware();
              drawText(1, 0, 0, "[API]-Set Zero");
              // ESP_LOGD("Zero", "Set zero: %s", getParamRequest.c_str());
              // ESP_LOGD("Zero", "Set zero");
              addLog("Set zero position");
              request->send(200, "text/html", "{\"status\": \"success\"}");
                return;
            }
            else if ((GetParamCommand == "angle") && (GlobalStateNow == STATE_IDLE || GlobalStateNow == STATE_API_HOLD || GlobalStateNow == STATE_API_UNHOLD))
            {
              String getParam = request->getParam("angle", true)->value(); 
              double ang = getParam.toDouble();
              drawText(1, 0, 0, "[API]-ENC->GetAngle"); 
              moveMotorAng(ang);
              request->send(200, "text/html", "{\"status\": \"success\"}");
                return;
            }
            else if (GetParamCommand == "getenc")
            {
              addLog("[API]-ENC->GetPos");
              drawText(1, 0, 0, "[API]-ENC->GetPos");
    
              String JsonSend = "{\"encoderPos\": \"" + String(getPosition(true)) + "\"}";
              request->send(200, "text/html", JsonSend);
            }
            else
            {

              
            }
          }
          else
          {
            addLog("[API]-Unauthorized");
            drawText(1, 0, 0, "[API]-Unauthorized");
            request->send(401, "text/html", "{\"status\": \"Unauthorized\"}");
          }
        }
      } });

      // Обробник для Control
      server.on("/control", HTTP_POST, [](AsyncWebServerRequest *request)
                {
                  GlobalStateUpdate();  

    if (request->hasParam("steps", true)) { 
        String steps = request->getParam("steps", true)->value(); 
        uint32_t stepsCount= steps.toInt(); 
        String checkpostParam = request->getParam("checkpost", true)->value();  
    ESP_LOGD("/control","steps- %s",steps); 
    ESP_LOGD("/control","checkpost- %s",checkpostParam); 

        // Виконання дій залежно від напрямку
        if (checkpostParam == "forward") {
            // Код для обертання вперед
            moveMotor(stepsCount,true,Global_prefAccelstep);
            GlobalStateNow = STATE_MOTOR_FORWARD;
            drawCircle(30,20,5); 
            drawText(1, 5, 5, "MOTOR -> FORWARD");

            addLog("Motor - Forward");
        } else if (checkpostParam == "backward") {
            moveMotor(stepsCount,false,Global_prefAccelstep);
           addLog("Motor - Backward");
           GlobalStateNow = STATE_MOTOR_BACKWARD;
           drawCircle(30,17,5);   
           drawText(1, 5, 5, "BACKWARD <- MOTOR");
            // Код для обертання назад
        } else if (checkpostParam == "hold") {
           digitalWrite(TMC_EN, LOW);
           GlobalStateNow = STATE_MOTOR_HOLD;
           addLog("Motor - Hold");
           drawCircle(30,20,5); 
           drawText(1, 5, 5, "MOTOR -- UNHOLD"); 
        }
        else if (checkpostParam == "unhold") {
           digitalWrite(TMC_EN, HIGH);
           GlobalStateNow = STATE_MOTOR_UNHOLD;
           addLog("Motor - Unhold"); 
           drawCircle(30,20,5); 
           drawText(1, 5, 5, "MOTOR -- UNHOLD");
        }
    }
    request->redirect("/control"); });

      // Обробник для G-Programing
      server.on("/gprograming", HTTP_POST, [](AsyncWebServerRequest *request)
                {
    if (request->hasParam("gcode", true)) {
        String gcode = request->getParam("gcode", true)->value();
        // Виконання отриманого G-коду
    }
    request->redirect("/gprograming"); });

      // Обробник для Logs (тільки GET, оскільки лог змінюється автоматично)
      server.on("/logs", HTTP_GET, [](AsyncWebServerRequest *request)
                { request->send(200, "text/html", buildHTML("logs", request)); });

      server.on("/apiinfo", HTTP_GET, [](AsyncWebServerRequest *request)
                { request->send(200, "text/html", buildHTML("apiinfo", request)); });

      // Обробник для Configuration

      ws.onEvent(onWebSocketEvent);
      server.addHandler(&ws);
      server.begin();
    }

    powerDeliveryControl(); // Ініціалізація контролю живлення
    voltagePD = "In. Voltage: " + String(readVoltagePD());
    drawText(0, 90, 0, voltagePD.c_str());
    logGlobalState();
  }

  void loop()
  {

    if (buttonStates[0])
    {
      // Serial.println("Button 1");
      if (!(currentStep < targetSteps))
      {
        setLed(LED1, true);
        setLed(LED2, false);
        drawText(1, 5, 5, "[SW] - LEFT");
        GlobalStateNow = STATE_API_FORWARD;
        moveMotor(100000, 1, Global_prefAccelstep);
      }
    }
    else if (buttonStates[2])
    {
      // Serial.println("Button 3");
      if (!(currentStep < targetSteps))
      {
        setLed(LED2, true);
        setLed(LED1, false);
        drawText(1, 5, 5, "[SW] - RIGHT");
        GlobalStateNow = STATE_API_BACKWARD;
        moveMotor(100000, 0, Global_prefAccelstep);
      }
    }

    if (stopSW1 || stopSW3)
    {
      stopSW1 = false;
      stopSW3 = false;
      setLed(LED1, false);
      setLed(LED2, false);
      targetSteps = 0;
    }

    if (holdBtn)
    {
      holdBtn = false;
      if (buttonStates[1])
      {
        digitalWrite(TMC_EN, LOW);
        GlobalStateNow = STATE_API_HOLD;
        drawText(1, 5, 5, "[SW] - HOLD");
        setLed(LED1, true);
        setLed(LED2, true);
      }
      else
      {
        digitalWrite(TMC_EN, HIGH);
        GlobalStateNow = STATE_API_UNHOLD;
        drawText(1, 5, 5, "[SW]- UNHOLD");
        setLed(LED1, false);
        setLed(LED2, false);
      }
    }

    if (GlobalStateNow == STATE_TIMERDISABLE)
    {
      GlobalStateNow = STATE_IDLE;
      ws.textAll(String(getPosition(false)));
      ESP_LOGD("TIMER", "DISBLE");
    }
    else if (GlobalStateNow == STATE_SETARROW)
    {
      GlobalStateNow = STATE_IDLE;
      float delta = angleArrow - getPosition(false);
      if (delta > 180)
      {
        delta -= 360;
      }
      else if (delta < -180)
      {
        delta += 360;
      }

      uint32_t steps = abs(delta / 1.8) * Global_prefDriverStep;

      bool direction;
      if (delta > 0)
      {
        direction = true;
      }
      else if (delta < 0)
      {
        direction = false;
      }
      else
      {
        steps = 0;
      }
      moveMotor(steps, direction, Global_prefAccelstep);
    }
  }
