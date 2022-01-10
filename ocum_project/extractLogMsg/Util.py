import pandas as pd
import spacy


class Util:
    @staticmethod
    def is_date_valid_NLP(line):
        import spacy

        nlp = spacy.load("en_core_web_sm")

        # print(line)
        try:
            doc = nlp(line)
            for entity in doc.ents:
                # print('{}: {}'.format(entity.label_, entity.text))
                return entity.text
        except:
            pass

    @staticmethod
    def is_date_valid(dt):
        try:
            pd.Timestamp(dt)
        except:
            return False
        else:
            return True
