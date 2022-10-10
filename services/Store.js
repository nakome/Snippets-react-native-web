import AsyncStorage from "@react-native-async-storage/async-storage";

const fetchData = async (url, config) => {
  const response = await fetch(url, config);
  const output = response.json();
  return output ? output : {};
};

export const storeData = async (value) => {
  try {
    const jsonValue = JSON.stringify(value);
    return await AsyncStorage.setItem("@app_settings", jsonValue);
  } catch (e) {
    // saving error
    console.log(e);
  }
};

export const getData = async () => {
  try {
    const jsonValue = await AsyncStorage.getItem("@app_settings");
    return jsonValue != null ? JSON.parse(jsonValue) : null;
  } catch (e) {
    // error reading value
    console.log(e);
  }
};

export const getDb = async (dbname, params) => {
  try {
    const storage = await getData();
    if (storage) {
      const urlBase = storage.url,
        url = `${urlBase}/g/${dbname}/?${params}`,
        config = {
          method: "GET",
          headers: {
            "Content-type": "application/json; charset=UTF-8",
            Authorization: `Bearer ${storage.token}`,
          },
        };
      const response = await fetchData(url, config);
      return response["DATA"];
    }
  } catch (error) {
    console.log(error);
  }
};

export const insertDb = async (dbname, params) => {
  const storage = await getData();
  const urlBase = storage.url,
    url = `${urlBase}/p/${dbname}`,
    config = {
      method: "POST",
      body: JSON.stringify(params),
      headers: {
        "Content-type": "application/json; charset=UTF-8",
        Authorization: `Bearer ${storage.token}`,
      },
    };
  const response = await fetchData(url, config);
  return response["MESSAGE"];
};

export const updateDb = async (dbname, uid, params) => {
  const storage = await getData();
  const urlBase = storage.url,
    url = `${urlBase}/u/${dbname}/?uid=${uid}`,
    config = {
      method: "POST",
      body: JSON.stringify(params),
      headers: {
        "Content-type": "application/json; charset=UTF-8",
        Authorization: `Bearer ${storage.token}`,
      },
    };
  const response = await fetchData(url, config);
  return response["MESSAGE"];
};

export const deleteDb = async (dbname, uid) => {
  const storage = await getData();
  const urlBase = storage.url,
    url = `${urlBase}/d/${dbname}/?uid=${uid}`,
    config = {
      method: "POST",
      body: JSON.stringify({}),
      headers: {
        "Content-type": "application/json; charset=UTF-8",
        Authorization: `Bearer ${storage.token}`,
      },
    };
  const response = await fetchData(url, config);
  return response["MESSAGE"];
};

export const createTableDb = async (dbname) => {
  const storage = await getData();
  const urlBase = storage.url,
    url = `${urlBase}/c/${dbname}`,
    config = {
      method: "POST",
      body: JSON.stringify({}),
      headers: {
        "Content-type": "application/json; charset=UTF-8",
        Authorization: `Bearer ${storage.token}`,
      },
    };
  const response = await fetchData(url, config);
  return response["MESSAGE"];
};

export const deleteTableDb = async (dbname) => {
  const storage = await getData();
  const urlBase = storage.url,
    url = `${urlBase}/destroy/${dbname}`,
    config = {
      method: "POST",
      body: JSON.stringify({}),
      headers: {
        "Content-type": "application/json; charset=UTF-8",
        Authorization: `Bearer ${storage.token}`,
      },
    };
  const response = await fetchData(url, config);
  return response["MESSAGE"];
};
