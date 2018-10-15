# symfony-test

This project is implemented using:
<ul>
<li>Symfony 4.1</li>
<li>RabbitMQ</li>
<li>MySql</li>
</ul>
<h3>Instructions</h3>
API is running on symfony dev server.
It can be started using symfony console command <code>php bin/console server:start</code>

The available requests are:
<ul>
<li>GET: path is <code>/events.{_format}</code>, available formats are <code>json</code> and <code>csv</code></li>
<li>POST: path is <code>/new/event</code>, with body in json format</li>
</ul>

POST request in our API require a body in format
<code>{event: <some event>, country: <some country>}</code>

<b>Since it is unknown how we determine top 5 countries, 
   they are set statically inside query, so in order for
   our app to return any result for our GET request there need to be at least
   one of bellow countries inside database.</b>
<p>US,CA,JP,FR,UK</p>

<h3>RabbitMQ</h3>
It is necessary to have RabbitMQ installed localy.
<p>Assuming that you have already installed RabbitMQ, it is necessary to create a virtual host and assign its permissions by issuing the following commands:</p>
<p><code>rabbitmqctl add_vhost symfony-test</code></p>
<p><code>rabbitmqctl set_permissions -p symfony-test guest ".*" ".*" ".*"</code></p>
<p>As an administrator, start and stop the server as usual for Debian-based systems: <code>service rabbitmq-server start</code></p>
<p>Next step is to run the following console command so that AMQPLIB can create the “data” exchange on RabbitMQ.</p>
<code>php bin/console rabbitmq:setup-fabric</code>
<p>In order to proccess the queue, run the command:</p>
<code>php bin/console rabbitmq:consumer task</code>

<h3>Note</h3>
<b>I used PostMan to send data</b>
