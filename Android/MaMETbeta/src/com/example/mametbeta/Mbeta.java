package com.example.mametbeta;


import java.util.ArrayList;
import java.util.List;
import java.text.SimpleDateFormat;
import java.util.Calendar;

import org.apache.commons.lang3.StringUtils;
import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.conn.params.ConnManagerParams;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.params.HttpConnectionParams;
import org.apache.http.params.HttpParams;
import org.json.JSONObject;

import android.annotation.SuppressLint;

import java.util.Locale;
import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.Reader;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.URL;

import android.app.Activity;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.location.LocationManager;
import android.location.LocationListener;
import android.location.Address;
import android.location.Criteria;
import android.location.Geocoder;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Handler;
import android.os.PowerManager;
import android.speech.RecognitionListener;
import android.speech.RecognizerIntent;
import android.speech.SpeechRecognizer;
import android.util.Log;
import android.view.Menu;
import android.speech.tts.TextToSpeech;
import android.telephony.SmsManager;
import android.widget.TextView;

@SuppressLint("SimpleDateFormat")
public class Mbeta extends Activity {

    private static final String DEBUG_TAG = "HttpExample";
    // variables
    public String baseURL = "https://agent.electricimp.com/";
    public String agentURL = "UFvljEJkfXEp";
    private TextView statustext;
    private TextView textView;
    
    private boolean isGetDataClicked;
    private int i =0;
    Handler mHandlertask = new Handler();

	String ID;
	String password;
	
	String phoneNumber = "085728755848";
	boolean traffic;
	
	//Location loc;
	//double latitude = location.getLatitude();
	//double longitude = location.getLongitude();
	public String latit = "-6.893211";
	public String longit = "107.610539";
 
	
	private static final String TAG = Mbeta.class.getName();

	//wakelock to keep screen on
	protected PowerManager.WakeLock mWakeLock;
	
	//speach recognizer for callbacks
	private SpeechRecognizer mSpeechRecognizer;

	//handler to post changes to progress bar
	private Handler mHandler = new Handler();

	//ui textview
	TextView responseText;

	//intent for speech recogniztion
	Intent mSpeechIntent;
	
	//this bool will record that it's time to kill P.A.L.
	boolean killCommanded = false;

	//legel commands
	private static final String[] VALID_COMMANDS = {
		"begin simulation",
		"yes i am okay",
		"no i am not",
		"where am i",
		"exit"
	};
	private static final int VALID_COMMANDS_SIZE = VALID_COMMANDS.length;

	static final String[] texts = {
		"are you okay", 
		"fine then be careful",
		"accident confirmed.  Begin message transmission",
		"bandung"
	};
	TextToSpeech tts;
	

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_mbeta);

		LocationManager locationManager;
		String context = Context.LOCATION_SERVICE;
		locationManager = (LocationManager)getSystemService(Context.LOCATION_SERVICE);
		
		Criteria criteria = new Criteria();
		criteria.setAccuracy(Criteria.ACCURACY_FINE);
		criteria.setAltitudeRequired(false);
		criteria.setBearingRequired(false);
		criteria.setCostAllowed(true);
		criteria.setPowerRequirement(Criteria.POWER_LOW);
		String provider = locationManager.getBestProvider(criteria, true);
		
		Location location =  locationManager.getLastKnownLocation(provider);
		
		//locationManager.requestLocationUpdates(provider, 2000, 10, locationListener);
		
		// setting for linking with layout variable
        statustext = (TextView) findViewById(R.id.statusvalue);
        textView = (TextView) findViewById(R.id.myText);
        // Execute the Runnable timedTask. can be placed somewhere else, i.e button click
        mHandlertask.post(timedTask);
	}

	@Override
	public void onStart() {
		LocationManager locationManager;
		String context = Context.LOCATION_SERVICE;
		locationManager = (LocationManager)getSystemService(Context.LOCATION_SERVICE);
		
		Criteria criteria = new Criteria();
		criteria.setAccuracy(Criteria.ACCURACY_FINE);
		criteria.setAltitudeRequired(false);
		criteria.setBearingRequired(false);
		criteria.setCostAllowed(true);
		criteria.setPowerRequirement(Criteria.POWER_LOW);
		String provider = locationManager.getBestProvider(criteria, true);
		
		Location location =  locationManager.getLastKnownLocation(provider);
		
		locationManager.requestLocationUpdates(provider, 2000, 10, locationListener);
		
		mSpeechRecognizer = SpeechRecognizer.createSpeechRecognizer(Mbeta.this);
		SpeechListener mRecognitionListener = new SpeechListener();
		mSpeechRecognizer.setRecognitionListener(mRecognitionListener);
		mSpeechIntent = new Intent(RecognizerIntent.ACTION_RECOGNIZE_SPEECH);

		mSpeechIntent.putExtra(RecognizerIntent.EXTRA_CALLING_PACKAGE,"com.example.voicecommand");

		// Given an hint to the recognizer about what the user is going to say
		mSpeechIntent.putExtra(RecognizerIntent.EXTRA_LANGUAGE_MODEL,
				RecognizerIntent.LANGUAGE_MODEL_FREE_FORM);

		// Specify how many results you want to receive. The results will be sorted
		// where the first result is the one with higher confidence.
		mSpeechIntent.putExtra(RecognizerIntent.EXTRA_MAX_RESULTS, 20);

		mSpeechIntent.putExtra(RecognizerIntent.EXTRA_PARTIAL_RESULTS, true);

		//aqcuire the wakelock to keep the screen on until user exits/closes app
		final PowerManager pm = (PowerManager) getSystemService(Context.POWER_SERVICE);
		this.mWakeLock = pm.newWakeLock(PowerManager.SCREEN_DIM_WAKE_LOCK, TAG);
		this.mWakeLock.acquire();
		mSpeechRecognizer.startListening(mSpeechIntent);
		super.onStart();
	}
	
	private String updateWithNewLocation(Location location) {
		Calendar c = Calendar.getInstance();
		SimpleDateFormat dfDate_day;
		dfDate_day= new SimpleDateFormat("HH:mm:ss dd/MM/yyyy");
		
		String latLongString;
		String addressString = "<Alamat tidak ditemukan>";
		
		if (location != null) {
			double lat = location.getLatitude();
			String latString = String.valueOf(lat);
			double lng = location.getLongitude();
			String lngString = String.valueOf(lng);
			latLongString = "Lat : " + latString + "\nLong: " + lngString;
			
			double latitude = location.getLatitude();
			double longitude = location.getLongitude();
			Geocoder gc = new Geocoder(this, Locale.getDefault());
			try {
				List<Address> addresses = gc.getFromLocation(lat, lng, 1);
				StringBuilder sb = new StringBuilder();
				if(addresses.size()>0) {
					Address address = addresses.get(0);
					
					for (int i=0; i<address.getMaxAddressLineIndex();i++)
						sb.append(address.getAddressLine(i)).append("\n");
						sb.append(address.getLocality()).append("\n");
						sb.append(address.getPostalCode()).append("\n");
						sb.append(address.getCountryName());
				}
				addressString = sb.toString();
			} catch (IOException e) {};
		}  else {
			latLongString = "<Lokasi tidak ditemukan>";
		}
		String message = "Pada waktu " + dfDate_day.format(c.getTime()) + " di" + addressString + "dengan koordinat GPS:" + latLongString + "terjadi kecelakaan yang menimpa pengguna nomor ini.";
		return message;
	}
	
	private final LocationListener locationListener = new LocationListener() {
		public void onLocationChanged(Location location) {
			updateWithNewLocation(location);
		}
		
		public void onProviderDisabled(String provider) { 
			updateWithNewLocation(null);
		}
		
		public void onProviderEnabled(String provider) { }
		public void onStatusChanged(String provider, int Status, Bundle extras) { }
		
	};
	
	
	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		return false;
	}
	
		   /** The time it takes for our client to timeout */
		    public static final int HTTP_TIMEOUT = 30 * 1000; // milliseconds

		    /** Single instance of our HttpClient */
		    private static HttpClient mHttpClient;

		    /**
		     * Get our single instance of our HttpClient object.
		     *
		     * @return an HttpClient object with connection parameters set
		     */
		    private static HttpClient getHttpClient() {
		        if (mHttpClient == null) {
		            mHttpClient = new DefaultHttpClient();
		            final HttpParams params = mHttpClient.getParams();
		            HttpConnectionParams.setConnectionTimeout(params, HTTP_TIMEOUT);
		            HttpConnectionParams.setSoTimeout(params, HTTP_TIMEOUT);
		            ConnManagerParams.setTimeout(params, HTTP_TIMEOUT);
		        }
		        return mHttpClient;
		    }
	
	public static String executeHttpPost(String url, ArrayList<NameValuePair> postParameters) throws Exception {
        BufferedReader in = null;
        try {
            HttpClient client = getHttpClient();
            HttpPost request = new HttpPost(url);
            UrlEncodedFormEntity formEntity = new UrlEncodedFormEntity(postParameters);
            request.setEntity(formEntity);
            HttpResponse response = client.execute(request);
            in = new BufferedReader(new InputStreamReader(response.getEntity().getContent()));

            StringBuffer sb = new StringBuffer("");
            String line = "";
            String NL = System.getProperty("line.separator");
            while ((line = in.readLine()) != null) {
                sb.append(line + NL);
            }
            in.close();

            String result = sb.toString();
            return result;
        } finally {
            if (in != null) {
                try {
                    in.close();
                } catch (IOException e) {
                    e.printStackTrace();
                }
            }
        }
    }

	
	public void insert() {
		ArrayList<NameValuePair> postParameters = new ArrayList<NameValuePair>();
		postParameters.add(new BasicNameValuePair("lat",latit));
		postParameters.add(new BasicNameValuePair("lng",longit));
		
		String response = null;
		//String gog = latit;
		//String gok = longit;
		
		try{
			response = executeHttpPost("http://192.168.0.2/mamet/kirim.php", postParameters);
			
			String res = response.toString();
			res = res.trim();
			res = res.replaceAll("\\s+", "");
			if(res.equals("1")) Log.e("Data Tersimpan", "simpan");
			else Log.e("Data tersimpan ke server", "server");
			
		}
		
		
		catch (Exception e) {
			Log.i("yeah", "wooho");
		}
		
	}
	
	String SENT = "SMS_SENT";
	String DELIVERED = "SMS_DELIVERED";
	public String getResponse(int command){
		String retString = "I'm sorry, sir. I'm afraid I can't do that.";
		PendingIntent sentPI = PendingIntent.getBroadcast(this, 0, new Intent(SENT), 0);
		PendingIntent deliveredPI = PendingIntent.getBroadcast(this, 0, new Intent(DELIVERED), 0);
		switch (command) {
		case 0:
			retString = texts[0];
			break;
		case 1:
			retString = texts[1];
			break;
		case 2:
			retString = texts[2];
			LocationManager locationManager;
			String context = Context.LOCATION_SERVICE;
			locationManager = (LocationManager)getSystemService(Context.LOCATION_SERVICE);
			
			Criteria criteria = new Criteria();
			criteria.setAccuracy(Criteria.ACCURACY_FINE);
			criteria.setAltitudeRequired(false);
			criteria.setBearingRequired(false);
			criteria.setCostAllowed(true);
			criteria.setPowerRequirement(Criteria.POWER_LOW);
			String provider = locationManager.getBestProvider(criteria, true);
			
			Location location =  locationManager.getLastKnownLocation(provider);
			
			
			//locationManager.requestLocationUpdates(provider, 2000, 10, locationListener);
			insert();
			
			
			String msg = updateWithNewLocation(location);
			TextView myLocation = (TextView)findViewById(R.id.GPSLocation);
			myLocation.setText(msg);
			//response = executeHttpPost("http://192.168.0.2/mamet/sendSMS.php", postParameters);
			SmsManager sms = SmsManager.getDefault();
			sms.sendTextMessage(phoneNumber, null, msg, sentPI, deliveredPI);
			break;
		case 3:
			retString = texts[3];
			break;
		case 4:
			killCommanded = true;
			break;

		default:
			break;
		}
		return retString;
	}
	

	protected void onResume(){
		super.onResume();
		tts = new TextToSpeech(Mbeta.this, new TextToSpeech.OnInitListener() {
				@Override
			public void onInit(int status) {
				 //TODO Auto-generated method stub
				if(status != TextToSpeech.ERROR){
					tts.setLanguage(Locale.ENGLISH);
				}
			}
		});
	}
	
	@Override
	protected void onPause() {
		//kill the voice recognizer
		if(mSpeechRecognizer != null){
			mSpeechRecognizer.destroy();
			mSpeechRecognizer = null;
		}
		if(tts != null){
			tts.stop();
			tts.shutdown();
		}
	
		this.mWakeLock.release();
		super.onPause();
	}
	

	private void processCommand(ArrayList<String> matchStrings){
		String response = "I'm sorry, sir. I'm afraid I can't do that.";
		int maxStrings = matchStrings.size();
		boolean resultFound = false;
		for(int i =0; i < VALID_COMMANDS_SIZE && !resultFound;i++){
			for(int j=0; j < maxStrings && !resultFound; j++){
				if(StringUtils.getLevenshteinDistance(matchStrings.get(j), VALID_COMMANDS[i]) <(VALID_COMMANDS[i].length() / 3) ){
					response = getResponse(i);
				}
			}
		}
		
		final String finalResponse = response;
		mHandler.post(new Runnable() {
			public void run() {
				//responseText.setText(finalResponse);
				tts.speak(finalResponse,TextToSpeech.QUEUE_FLUSH, null);
			}
		});

	}
	class SpeechListener implements RecognitionListener {
		public void onBufferReceived(byte[] buffer) {
			Log.d(TAG, "buffer received ");
		}
		public void onError(int error) {
			//if critical error then exit
			if(error == SpeechRecognizer.ERROR_CLIENT || error == SpeechRecognizer.ERROR_INSUFFICIENT_PERMISSIONS){
				Log.d(TAG, "client error");
			}
			//else ask to repeats
			else{
				Log.d(TAG, "other error");
				mSpeechRecognizer.startListening(mSpeechIntent);
			}
		}
		public void onEvent(int eventType, Bundle params) {
			Log.d(TAG, "onEvent");
		}
		public void onPartialResults(Bundle partialResults) {
			Log.d(TAG, "partial results");
		}
		public void onReadyForSpeech(Bundle params) {
			Log.d(TAG, "on ready for speech");
		}
		public void onResults(Bundle results) {
			Log.d(TAG, "on results");
			ArrayList<String> matches = null;
			if(results != null){
				matches = results.getStringArrayList(SpeechRecognizer.RESULTS_RECOGNITION);
				if(matches != null){
					Log.d(TAG, "results are " + matches.toString());
					final ArrayList<String> matchesStrings = matches;
					processCommand(matchesStrings);
					if(!killCommanded)
						mSpeechRecognizer.startListening(mSpeechIntent);
					else
						finish();

				}
			}

		}
		public void onRmsChanged(float rmsdB) {
			//			Log.d(TAG, "rms changed");
		}
		public void onBeginningOfSpeech() {
			Log.d(TAG, "speach begining");
		}
		public void onEndOfSpeech() {
			Log.d(TAG, "speach done");
		}

	};
	
	// =================================================================================
    // This is for Getting response with plain text

    private class DownloadWebPageTask extends AsyncTask<String, Void, String> {
        @Override
        protected String doInBackground(String... urls){
            //params comes from the execute()
            try{
                obj = new HandleJSON(urls[0]);
                return downloadUrl(urls[0]);
            }catch (IOException e){
                return "Unable to retrieve web page. URL may be invalid";
            }
        }
        //onPostExecute display the result of AsyncTask
        @Override
        protected void onPostExecute(String result){
            //textView.setText(result);
            obj.readAndParseJSON(result);
            //axtext.setText(obj.getAx());
            //aytext.setText(obj.getAy());
            //aztext.setText(obj.getAz());
            //gxtext.setText(obj.getGx());
            //gytext.setText(obj.getGy());
            //gztext.setText(obj.getGz());
            statustext.setText(obj.getStatus());
        }
    }

    private String downloadUrl (String myurl) throws IOException{
        InputStream stream = null;
        int len = 500; //length of content
        try{
            URL url = new URL(myurl);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setReadTimeout(10000);
            conn.setConnectTimeout(15000);
            conn.setRequestMethod("GET");
            conn.setDoInput(true);

            //start the query
            conn.connect();
            int response = conn.getResponseCode();
            Log.d(DEBUG_TAG, "The response is:" + response);

            stream = conn.getInputStream();

            //convert to string
            String contentAsString = readIt (stream,len);
            return contentAsString;

            //make sure the inputstream is closed after used by app
        }finally{
            if(stream!=null)
                stream.close();
        }
    }

    public String readIt(InputStream stream, int len) throws IOException, UnsupportedEncodingException{
        Reader reader = null;
        reader = new InputStreamReader(stream, "UTF-8");
        char[] buffer = new char[len];
        reader.read(buffer);
        return new String(buffer);
    }
    //==============================================================================

  //==============================================================================
    // This is for Getting response in form of JSON, manual request

    private HandleJSON obj;

    public class HandleJSON {
        private String ax = "ax";
        private String ay = "ay";
        private String az = "az";
        private String gx = "gx";
        private String gy = "gy";
        private String gz = "gz";
        private String temp = "temp";
        private String tempC = "tempC";
        private String tempF = "tempF";
        private String status = "status";
        private String urlString = null;

        public volatile boolean parsingComplete = true;
        public HandleJSON(String url){
            this.urlString = url;
        }
        public String getAx(){
            return ax;
        }
        public String getAy(){
            return ay;
        }
        public String getAz(){
            return az;
        }
        public String getGx(){
            return gx;
        }
        public String getGy(){
            return gy;
        }
        public String getGz(){
            return gz;
        }
        public String getTemp(){
            return temp;
        }
        public String getTempC(){
            return tempC;
        }
        public String getTempF(){
            return tempF;
        }
        public String getStatus(){
            return status;
        }

        @SuppressLint("NewApi")
        public void readAndParseJSON(String in) {
            try {
                JSONObject reader = new JSONObject(in);
                ax = reader.getString("ax");
                ay = reader.getString("ay");
                az = reader.getString("az");
                gx = reader.getString("gx");
                gy = reader.getString("gy");
                gz = reader.getString("gz");
                temp = reader.getString("temp");
                tempC = reader.getString("tempC");
                tempF = reader.getString("tempF");
                status = reader.getString("status");

                parsingComplete = false;
            } catch (Exception e) {
                // TODO Auto-generated catch block
                e.printStackTrace();
            }

        }

        // can be deleted since can be replaced tih downloadUrl
        public void fetchJSON(String myurl){
            Thread thread = new Thread(new Runnable(){
                @Override
                public void run() {
                    try {
                        URL url = new URL(urlString);
                        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                        conn.setReadTimeout(10000 /* milliseconds */);
                        conn.setConnectTimeout(15000 /* milliseconds */);
                        conn.setRequestMethod("GET");
                        conn.setDoInput(true);
                        // Starts the query
                        conn.connect();
                        InputStream stream = conn.getInputStream();

                        String data = convertStreamToString(stream);

                        readAndParseJSON(data);
                        stream.close();

                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                }
            });

            thread.start();
        }
        public String convertStreamToString(java.io.InputStream is) {
            java.util.Scanner s = new java.util.Scanner(is).useDelimiter("\\A");
            return s.hasNext() ? s.next() : "";
        }
    }
    //=============================================================================
  
    //============================================================================
    // Handler for loop process

    private final Runnable timedTask = new Runnable() {
        @Override
        public void run() {
            // Time variable for looping runnable
            mHandlertask.postDelayed(timedTask,1000);

            // looping for getting data
            //if (isGetDataClicked==true){
                ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
                NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
                if (networkInfo!= null && networkInfo.isConnected()){
                    new DownloadWebPageTask().execute(baseURL+agentURL);
                    //urlsentText.setText(baseURL+agentURL);
                }
                else
                	textView.setText("No Network connection available");
            //}
            //else
            //    ;//textView.setText("Click Get Data to start getting data");
        }
    };

}