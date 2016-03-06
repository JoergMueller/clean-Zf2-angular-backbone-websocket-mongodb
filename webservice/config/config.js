
module.exports.local = {

    enviroment: 'local',

    // port to start the app on
    port: 8080,
    authPort: 3131,
    serverName: 'serverName',
    serverUrl: 'http://localhost.site',

    session: {
        secret: '#wQL#lO6Y%mJ6K9f' + new Date().getTime(),
        name: 'mSIDWS',
        store: '',
        proxy: true,
        resave: true,
        saveUninitialized: true,
        cookie: {
            domain: '.localhost.site',
            path: '/',
            httpOnly: true,
            secure: false,
            maxAge: null
        }
    },

    db: 'mongodb://username:password@localhost:27017/dev_database',
    dbopts: {}

};
