import mysql from 'mysql2/promise';

const DATABASE_URL = "mysql://root:root@localhost:3306/ncnhsdb";

async function testConnection() {
    try {
        console.log('Attempting to connect to database...');
        const connection = await mysql.createConnection(DATABASE_URL);
        console.log('✅ Database connected successfully!');
        
        // Test a simple query
        const [rows] = await connection.execute('SELECT 1 as test');
        console.log('✅ Query test successful:', rows);
        
        await connection.end();
        console.log('✅ Connection closed properly');
    } catch (error) {
        console.error('❌ Database connection failed:');
        console.error('Error code:', error.code);
        console.error('Error message:', error.message);
        console.error('SQL State:', error.sqlState);
        
        if (error.code === 'ER_BAD_DB_ERROR') {
            console.log('\n💡 Suggestion: The database "ncnhsdb" does not exist.');
            console.log('   Try creating it or check your DATABASE_URL in .env file');
        } else if (error.code === 'ER_ACCESS_DENIED_ERROR') {
            console.log('\n💡 Suggestion: Wrong username/password.');
            console.log('   Check your credentials in .env file');
        } else if (error.code === 'ECONNREFUSED') {
            console.log('\n💡 Suggestion: MySQL server is not running or wrong host/port.');
        }
    }
}

testConnection();
