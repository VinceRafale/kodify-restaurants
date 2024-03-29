class development inherits development::params {
    stage { 'yum': before => Stage[main] }
    class { 'yum_prod::centos': stage => yum }
    class { 'yum_prod::epel': stage => yum }
    class { 'yum_prod::ius': stage => yum }


    include mysql
    include development::httpd


    class { 'timezone': timezone => 'UTC' }

    class { "php_prod":
        php => "php54"
    }
  
    include development::tools

    # disable firewall, which is enabled in newer basebox
    service { "iptables": ensure => stopped, enable => false }

    file { "/var/www/log":
      owner  => apache,
      group  => apache,
      mode   => 777,
      ensure => directory,
      require => [ Package["httpd"] ]
    }

    file { "/etc/php.d/php.ini":
      owner  => root,
      group  => root,
      mode   => 644,
      source => "puppet:///modules/development/php.ini",
      require => [ Package["httpd"], Package["php54"] ],
      notify => Service["httpd"]
    }

    package { 'cronie':
        ensure => $version,
        alias => 'cronie',
        require => Yumrepo['ius'],
    }

    service { 'crond':
        ensure    => running,
        enable    => true,
        require   => Package['cronie'],
    }

    # preferred symlink syntax
    file { '/etc/cron.d':
       ensure => 'link',
       target => '/var/www/katt/app/config/cron/dev',
       require => [ Package["cronie"]],
       notify => Service["crond"]
    }

}

node default{
	include development
}

