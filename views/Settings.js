import { useNavigate } from "react-router-native";
import {
  Text,
  View,
  Button,
  Picker,
  TextInput,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  ActivityIndicator,
  Modal,
} from "react-native";

import { useEffect, useState, useContext } from "react";
import { Ionicons } from "@expo/vector-icons";
// context
import ThemeOptions from "../services/ThemeOptions";
// theme options
import { ThemeStyleColors } from "../config/Theme";
// storage
import { storeData } from "../services/Store";
// sleep
import Sleep from "../utils/Sleep";

export default function Create() {
  const navigate = useNavigate();
  const config = useContext(ThemeOptions);
  const [loaded, setLoaded] = useState(false);
  // set values
  const [author, setAuthor] = useState(config.data.author);
  const [authorInfo, setAuthorInfo] = useState(config.data.authorInfo);
  const [apiUrl, setApiUrl] = useState(config.data.url);
  const [apiToken, setApiToken] = useState(config.data.token);
  const [lang, setLang] = useState(config.data.language);
  const [theme, setTheme] = useState(config.data.theme);

  // modal
  const [isVisible, setIsVisible] = useState(false);
  const [msg, setMsg] = useState("");

  const handleBtnSave = () => {
    const arr = {
      author: author,
      authorInfo: authorInfo,
      theme: theme,
      url: apiUrl,
      token: apiToken,
      language: lang,
    };
    storeData(arr).then(() => {
      setIsVisible(true);
      setMsg(config.lang.settingsSuccessSave);
      Sleep(1000).then(() => {
        location.href = location.href;
      });
    });
  };

  useEffect(() => setLoaded(true), []);

  if (!loaded) {
    return (
      <View style={[styles.container, styles.horizontal]}>
        <ActivityIndicator size="large" color={ThemeStyleColors.warning} />
      </View>
    );
  }

  return (
    loaded && (
      <SafeAreaView style={styles.container}>
        <Modal
          transparent
          animationType={"fade"}
          onRequestClose={() => setIsVisible(false)}
          visible={isVisible}
        >
          <View style={styles.modal}>
            <Text style={styles.modalTitle}>{msg}</Text>
          </View>
        </Modal>
        <ScrollView>
          <View style={styles.box}>
            <Text style={styles.boxTitle}>{config.lang.settingsTitle}</Text>
            <Text style={styles.boxDesc}>{config.lang.settingsSubtitle}</Text>
          </View>

          <Text style={styles.label}>{config.lang.labelName}</Text>
          <TextInput
            maxLength={40}
            style={styles.input}
            onChangeText={(txt) => setAuthor(txt)}
            value={author}
          />

          <Text style={styles.label}>{config.lang.labelBio}</Text>
          <TextInput
            multiline
            numberOfLines={10}
            style={[styles.input, { height: 150 }]}
            onChangeText={(txt) => setAuthorInfo(txt)}
            value={authorInfo}
          />

          <Text style={styles.label}>{config.lang.labelApiUrl}</Text>
          <TextInput
            style={styles.input}
            onChangeText={(txt) => setApiUrl(txt)}
            value={apiUrl}
          />

          <Text style={styles.label}>{config.lang.labelToken}</Text>
          <TextInput
            style={styles.input}
            onChangeText={(txt) => setApiToken(txt)}
            value={apiToken}
          />

          <Text style={styles.label}>{config.lang.selectLanguage}</Text>
          <Picker
            selectedValue={lang}
            style={styles.picker}
            onValueChange={(itemValue, itemIndex) => setLang(itemValue)}
          >
            <Picker.Item label="Español" value="es" />
            <Picker.Item label="English" value="en" />
          </Picker>

          <View style={styles.buttons}>
            <Button
              color={ThemeStyleColors.primary}
              title={[
                <Ionicons
                  name="save"
                  size={16}
                  color={ThemeStyleColors.info}
                  style={{ marginRight: 5 }}
                />,
                config.lang.save,
              ]}
              onPress={handleBtnSave}
            />
          </View>
        </ScrollView>
      </SafeAreaView>
    )
  );
}

const styles = StyleSheet.create({
  horizontal: {
    flexDirection: "row",
    justifyContent: "space-around",
    padding: 10,
  },
  container: {
    flex: 1,
    justifyContent: "flex-start",
    padding: 10,
    width: "100%",
    maxWidth: 768,
  },
  input: {
    height: 40,
    marginLeft: 12,
    marginRight: 12,
    marginBottom: 12,
    borderWidth: 1,
    padding: 10,
    backgroundColor: ThemeStyleColors.primary,
    borderColor: ThemeStyleColors.info,
    color: ThemeStyleColors.info,
  },
  picker: {
    height: 40,
    marginLeft: 12,
    marginRight: 12,
    marginBottom: 12,
    borderWidth: 1,
    padding: 5,
    backgroundColor: ThemeStyleColors.primary,
    borderColor: ThemeStyleColors.info,
    color: ThemeStyleColors.info,
  },
  content: {
    marginLeft: 12,
    marginRight: 12,
    marginBottom: 12,
    borderWidth: 1,
    padding: 10,
    backgroundColor: ThemeStyleColors.primary,
    borderColor: ThemeStyleColors.info,
    color: ThemeStyleColors.info,
  },
  buttons: {
    margin: 12,
    display: "flex",
    flexDirection: "row",
    justifyContent: "space-between",
    maxWidth: 150,
  },
  label: {
    marginLeft: 12,
    marginRight: 12,
    marginTop: 12,
    marginBottom: 5,
    color: ThemeStyleColors.warning,
  },
  modal: {
    backgroundColor: ThemeStyleColors.primary,
    borderColor: ThemeStyleColors.primary,
    borderRadius: 4,
    borderWidth: 1,
    padding: 10,
  },
  modalTitle: {
    fontSize: 18,
    margin: 12,
    color: ThemeStyleColors.warning,
  },
  box: {
    margin: 12,
    marginBottom: 5,
    padding: 5,
    backgroundColor: ThemeStyleColors.warning,
  },
  boxTitle: {
    fontSize: 18,
    fontWeight: "bold",
    color: ThemeStyleColors.primary,
  },
  boxDesc: {
    fontSize: 16,
    color: ThemeStyleColors.secondary,
  },
});
